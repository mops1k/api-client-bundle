<?php

namespace ApiClientBundle\Tests;

use ApiClientBundle\Client\ListResponseInterface;
use ApiClientBundle\Enum\HttpResponseStatusEnum;
use ApiClientBundle\Exception\HttpRequestException;
use ApiClientBundle\Exception\ServerErrorException;
use ApiClientBundle\HTTP\Context\ContextStorage;
use ApiClientBundle\HTTP\HttpClient;
use ApiClientBundle\HTTP\HttpClientInterface;
use ApiClientBundle\HTTP\HttpNetworkException;
use ApiClientBundle\Tests\Mock\ListQuery;
use ApiClientBundle\Tests\Mock\ListResponse;
use ApiClientBundle\Tests\Mock\Query;
use ApiClientBundle\Tests\Mock\QueryWithFile;
use ApiClientBundle\Tests\Mock\Response;
use ApiClientBundle\Tests\Mock\ResponseWithFile;
use ApiClientBundle\Tests\Stubs\Kernel;
use GuzzleHttp\Psr7\Response as HttpResponse;
use Http\Client\Exception\NetworkException;
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
        $reflectionProperty->setValue($client, $this->mockHttpClient);

        $this->client = $client;
    }

    public function testHttpClientRequestSuccess(): void
    {
        $mockResponseContents = \file_get_contents(__DIR__ . '/Stubs/Response/ok.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $this->mockHttpClient->on($requestMatcher, function () use ($mockResponseContents, $mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(HttpResponseStatusEnum::STATUS_200->getCode());
            $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        $response = $this->client->request(new Query());
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('Ok!', $response->status);
    }

    public function testHttpClientContextWithRequestSuccess(): void
    {
        $mockResponseContents = \file_get_contents(__DIR__ . '/Stubs/Response/ok.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $builtRequest = $builtResponse = null;
        $this->mockHttpClient->on(
            $requestMatcher,
            function (RequestInterface $request) use ($mockResponseContents, $mockResponse, &$builtRequest, &$builtResponse) {
                $mockResponse->method('getStatusCode')->willReturn(HttpResponseStatusEnum::STATUS_200->getCode());
                $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
                $mockResponse->method('getBody')->willReturn($streamMock);

                $builtRequest = $request;
                $builtResponse = $mockResponse;

                return $mockResponse;
            }
        );

        $query = new Query();
        $response = $this->client->request($query);
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('Ok!', $response->status);

        $context = ContextStorage::get($query);
        self::assertSame($builtRequest, $context->getRequest());
        self::assertSame($builtResponse, $context->getResponse());

        ContextStorage::clear();
        self::assertNull(ContextStorage::get($query));
    }

    public function testHttpClientCollectionRequestSuccess(): void
    {
        $mockResponseContents = \file_get_contents(__DIR__ . '/Stubs/Response/collection.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $this->mockHttpClient->on($requestMatcher, function () use ($mockResponseContents, $mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(HttpResponseStatusEnum::STATUS_200->getCode());
            $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        $response = $this->client->request(new ListQuery());
        self::assertInstanceOf(ListResponseInterface::class, $response);
        self::assertInstanceOf(ListResponse::class, $response);
        self::assertEquals(\json_decode($mockResponseContents, true, 512, JSON_THROW_ON_ERROR), $response->data);
    }

    public function testHttpClientRequestWithFileSuccess(): void
    {
        $mockResponseContents = \file_get_contents(__DIR__ . '/Stubs/Response/file.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $this->mockHttpClient->on($requestMatcher, function () use ($mockResponseContents, $mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(HttpResponseStatusEnum::STATUS_200->getCode());
            $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        $response = $this->client->request(new QueryWithFile());
        self::assertInstanceOf(ResponseWithFile::class, $response);
        self::assertEquals('image.png', $response->FileName);
        self::assertEquals('png', $response->FileExt);
        self::assertEquals(95, $response->FileSize);
        self::assertEquals('lskai9gx46yftdle5nhrocbl39jsqn9b', $response->FileId);
        self::assertEquals('https://v2.convertapi.com/d/lskai9gx46yftdle5nhrocbl39jsqn9b', $response->Url);
    }

    public function testHttpClientSerializationFail(): void
    {
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $this->mockHttpClient->on($requestMatcher, function () use ($mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(HttpResponseStatusEnum::STATUS_200->getCode());
            $streamMock = (new Psr17Factory())->createStream('fail=true');
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        $query = new Query();
        $this->expectExceptionMessage('Http client serialization error for incoming data: "fail=true".');
        $this->client->request($query);
    }

    public function testHttpClientRequestServerError(): void
    {
        $mockResponseContents = \file_get_contents(__DIR__ . '/Stubs/Response/ok.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $this->mockHttpClient->on($requestMatcher, function () use ($mockResponseContents, $mockResponse) {
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
        $this->mockHttpClient->on($requestMatcher, function () use ($mockResponseContents, $mockResponse) {
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

    public function testHttpClientNetworkHttpError(): void
    {
        $requestMatcher = new RequestMatcher();
        $builtRequest = null;
        $this->mockHttpClient->on($requestMatcher, function (RequestInterface $request) use (&$builtRequest) {
            $builtRequest = $request;

            throw new NetworkException('NetworkException', $request);
        });

        try {
            $this->client->request(new Query());
        } catch (HttpNetworkException $exception) {
            self::assertSame($builtRequest, $exception->getRequest());
        }
    }
}
