<?php

namespace ApiClientBundle\Tests;

use ApiClientBundle\Client\CollectionResponseInterface;
use ApiClientBundle\Enum\HttpResponseStatusEnum;
use ApiClientBundle\Exception\HttpRequestException;
use ApiClientBundle\Exception\ServerErrorException;
use ApiClientBundle\HTTP\HttpClient;
use ApiClientBundle\HTTP\HttpClientInterface;
use ApiClientBundle\Tests\Mock\CollectionQuery;
use ApiClientBundle\Tests\Mock\CollectionResponse;
use ApiClientBundle\Tests\Mock\Query;
use ApiClientBundle\Tests\Mock\QueryWithFile;
use ApiClientBundle\Tests\Mock\Response;
use ApiClientBundle\Tests\Mock\ResponseWithFile;
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
        $client = self::getContainer()->get(HttpClientInterface::class);

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

    public function testHttpClientCollectionRequestSuccess(): void
    {
        $mockResponseContents = \file_get_contents(__DIR__ . '/Stubs/Response/collection.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $this->mockHttpClient->on($requestMatcher, function (RequestInterface $request) use ($mockResponseContents, $mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(HttpResponseStatusEnum::STATUS_200->getCode());
            $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        /** @var CollectionResponse $response */
        $response = $this->client->request(new CollectionQuery());
        self::assertInstanceOf(CollectionResponseInterface::class, $response);
        self::assertEquals(\json_decode($mockResponseContents, true, 512, JSON_THROW_ON_ERROR), $response->data);
    }

    public function testHttpClientRequestWithFileSuccess(): void
    {
        $mockResponseContents = \file_get_contents(__DIR__ . '/Stubs/Response/file.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $this->mockHttpClient->on($requestMatcher, function (RequestInterface $request) use ($mockResponseContents, $mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(HttpResponseStatusEnum::STATUS_200->getCode());
            $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        /** @var ResponseWithFile $response */
        $response = $this->client->request(new QueryWithFile());
        self::assertInstanceOf(ResponseWithFile::class, $response);
        self::assertEquals('image.png', $response->FileName);
        self::assertEquals('png', $response->FileExt);
        self::assertEquals(95, $response->FileSize);
        self::assertEquals('lskai9gx46yftdle5nhrocbl39jsqn9b', $response->FileId);
        self::assertEquals('https://v2.convertapi.com/d/lskai9gx46yftdle5nhrocbl39jsqn9b', $response->Url);
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
