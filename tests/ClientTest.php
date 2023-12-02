<?php

namespace ApiClientBundle\Tests;

use ApiClientBundle\Enum\HttpResponseStatusEnum;
use ApiClientBundle\Exception\HttpRequestException;
use ApiClientBundle\Exception\ServerErrorException;
use ApiClientBundle\HTTP\HttpClient;
use ApiClientBundle\Tests\Mock\Query;
use ApiClientBundle\Tests\Mock\Response;
use ApiClientBundle\Tests\Stubs\Kernel;
use GuzzleHttp\Psr7\Response as HttpResponse;
use Http\Discovery\Psr17Factory;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Http\Message\RequestMatcher\RequestMatcher;
use Http\Mock\Client as MockHttpClient;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClientTest extends KernelTestCase
{
    protected HttpClient $client;
    protected MockHttpClient $mockHttpClient;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        Psr18ClientDiscovery::prependStrategy(MockClientStrategy::class);
        /** @phpstan-ignore-next-line */
        $this->mockHttpClient = Psr18ClientDiscovery::find();
        self::assertInstanceOf(MockHttpClient::class, $this->mockHttpClient);

        /** @var HttpClient $client */
        $client = self::getContainer()->get(HttpClient::class);

        $reflectionProperty = new \ReflectionProperty($client, 'client');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($client, $this->mockHttpClient);

        $this->client = $client;
    }

    public function testHttpClientRequestSuccess(): void
    {
        $mockResponseContents = \file_get_contents(__DIR__ . '/Stubs/Response/ok.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $this->mockHttpClient->on($requestMatcher, function (RequestInterface $request) use ($mockResponseContents, $mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(HttpResponseStatusEnum::STATUS_200->getCode());
            $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        /** @var Response $response */
        $response = $this->client->request(new Query());
        self::assertEquals('Ok!', $response->status);
    }

    public function testHttpClientRequestServerError(): void
    {
        $mockResponseContents = \file_get_contents(__DIR__ . '/Stubs/Response/ok.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $this->mockHttpClient->on($requestMatcher, function (RequestInterface $request) use ($mockResponseContents, $mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(HttpResponseStatusEnum::STATUS_500->getCode());
            $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        try {
            $this->client->request(new Query());
        } catch (ServerErrorException $exception) {
            self::assertEquals($exception->getMessage(), HttpResponseStatusEnum::STATUS_500->value);
            self::assertEquals($exception->getCode(), HttpResponseStatusEnum::STATUS_500->getCode());
        }
    }

    public function testHttpClientRequestHttpError(): void
    {
        $mockResponseContents = \file_get_contents(__DIR__ . '/Stubs/Response/ok.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $this->mockHttpClient->on($requestMatcher, function (RequestInterface $request) use ($mockResponseContents, $mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(HttpResponseStatusEnum::STATUS_404->getCode());
            $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        try {
            $this->client->request(new Query());
        } catch (HttpRequestException $exception) {
            self::assertSame($mockResponse, $exception->getResponse());
            self::assertEquals($exception->getMessage(), HttpResponseStatusEnum::STATUS_404->value);
            self::assertEquals($exception->getCode(), HttpResponseStatusEnum::STATUS_404->getCode());
        }
    }
}
