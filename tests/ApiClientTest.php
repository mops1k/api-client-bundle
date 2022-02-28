<?php

namespace ApiClientBundle\Tests;

use ApiClientBundle\Exceptions\ErrorResponseException;
use ApiClientBundle\Exceptions\QueryException;
use ApiClientBundle\Http\Client;
use ApiClientBundle\Http\ResponseFactory;
use ApiClientBundle\Interfaces\GenericErrorResponseInterface;
use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Model\AbstractResponse;
use ApiClientBundle\Model\GenericErrorResponse;
use ApiClientBundle\Service\ApiClientFactory;
use ApiClientBundle\Tests\Fixtures\SerializedNameQuery;
use ApiClientBundle\Tests\Fixtures\SerializedNameResponse;
use ApiClientBundle\Tests\Fixtures\TestClient;
use ApiClientBundle\Tests\Fixtures\TestErrorResponse;
use ApiClientBundle\Tests\Fixtures\TestQuery;
use ApiClientBundle\Tests\Fixtures\TestResponse;
use ProxyManager\Proxy\GhostObjectInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-type TAssertCallable callable(array<mixed>,object):bool
 */
class ApiClientTest extends KernelTestCase
{
    /**
     * @dataProvider responseDataProvider
     *
     * @template TResponse of object
     * @template TErrorResponse of GenericErrorResponse
     *
     * @param QueryInterface<TResponse, TErrorResponse> $query
     * @param array<mixed>|string $responseData
     * @param class-string<AbstractResponse> $expectedResponseInstance
     * @param TAssertCallable|null $responseAssertion
     */
    public function testSendSyncRequest(
        QueryInterface $query,
        array|string $responseData,
        int $statusCode,
        string $expectedResponseInstance,
        ?callable $responseAssertion = null,
    ): void {
        $mockResponse = new MockResponse(json_encode($responseData, JSON_THROW_ON_ERROR), ['http_code' => $statusCode]);

        $client = $this->createMockedApiClient($mockResponse, false)->use(TestClient::class);
        $response = $client->request($query);

        static::assertInstanceOf($expectedResponseInstance, $response);
        static::assertEquals($statusCode, $response->getStatusCode());
        if (null !== $responseAssertion) {
            static::assertTrue($responseAssertion($responseData, $response));
        }
    }

    /**
     * @dataProvider responseDataProvider
     *
     * @template TResponse of object
     * @template TErrorResponse of GenericErrorResponse
     *
     * @param QueryInterface<TResponse, TErrorResponse> $queryObject
     * @param array<mixed>|string $responseData
     * @param class-string<AbstractResponse> $expectedResponseInstance
     * @param TAssertCallable|null $responseAssertion
     */
    public function testSendAsyncRequest(
        QueryInterface $queryObject,
        array|string $responseData,
        int $statusCode,
        string $expectedResponseInstance,
        ?callable $responseAssertion = null,
    ): void {
        $mockResponse = new MockResponse(json_encode($responseData, JSON_THROW_ON_ERROR), ['http_code' => $statusCode]);

        $client = $this->createMockedApiClient($mockResponse, true)->use(TestClient::class);
        $query = new TestQuery();
        $response = $client->request($query);
        static::assertInstanceOf(GhostObjectInterface::class, $response);
        static::assertFalse($response->isProxyInitialized());

        try {
            static::assertInstanceOf(TestResponse::class, $response);
            static::assertEquals($statusCode, $response->getStatusCode());

            if (isset($responseData['status'])) {
                static::assertEquals($responseData['status'], $response->getStatus());
            }
            static::assertTrue($response->isProxyInitialized());
        } catch (GenericErrorResponseInterface $exception) {
            self::assertInstanceOf(ErrorResponseException::class, $exception);
            static::assertEquals($statusCode, $exception->getStatusCode());
            static::assertEquals(json_encode($responseData, JSON_THROW_ON_ERROR), $exception->getRawContent());
            static::assertEquals(Response::$statusTexts[$statusCode], $exception->getMessage());
            static::assertInstanceOf(GhostObjectInterface::class, $response);
            static::assertFalse($response->isProxyInitialized());
        }
    }

    public function testSimulateTransportException(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $mockResponse->cancel();

        $client = $this->createMockedApiClient($mockResponse, true)->use(TestClient::class);
        $query = new TestQuery();
        $response = $client->request($query);

        static::assertInstanceOf(GhostObjectInterface::class, $response);
        static::assertFalse($response->isProxyInitialized());

        $this->expectException(QueryException::class);
        static::assertInstanceOf(TestResponse::class, $response);
        static::assertEquals(500, $response->getStatusCode());
    }

    public function testFilesUploading(): void
    {
        $mockResponse = new MockResponse(\json_encode(['status' => false]), ['http_code' => 200]);

        $client = $this->createMockedApiClient($mockResponse, true)->use(TestClient::class);
        $query = new TestQuery(Request::METHOD_POST);
        $query->files()->set('test_file', __DIR__ . '/Fixtures/stub.txt');
        $response = $client->request($query);

        static::assertInstanceOf(GhostObjectInterface::class, $response);
        static::assertFalse($response->isProxyInitialized());

        static::assertInstanceOf(TestResponse::class, $response);
        static::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return iterable<array{
     *     query: QueryInterface,
     *     responseData: array<mixed>,
     *     statusCode: int,
     *     expectedResponseInstance: class-string<AbstractResponse>,
     *     assert?: TAssertCallable
     * }>
     */
    public function responseDataProvider(): iterable
    {
        $assertion1 = static fn (array $responseData, TestResponse $response): bool => $responseData['status'] === $response->getStatus();
        $assertion2 = static fn (array $responseData, TestErrorResponse $response): bool => $responseData['status'] === $response->getStatus();

        yield [
            'query' => new TestQuery(),
            'responseData' => ['status' => false],
            'statusCode' => 500,
            'expectedResponseInstance' => TestErrorResponse::class,
            'assert' => static fn (array $responseData, TestErrorResponse $response): bool => $response->getRawContent() === \json_encode(
                $responseData,
                JSON_THROW_ON_ERROR
            ),
        ];
        yield [
            'query' => new TestQuery(),
            'responseData' => ['status' => true],
            'statusCode' => 200,
            'expectedResponseInstance' => TestResponse::class,
            'assert' => $assertion1,
        ];
        yield [
            'query' => new TestQuery(),
            'responseData' => ['status' => false],
            'statusCode' => 400,
            'expectedResponseInstance' => TestErrorResponse::class,
            'assert' => $assertion2,
        ];
        yield [
            'query' => new TestQuery(),
            'responseData' => ['status' => false],
            'statusCode' => 404,
            'expectedResponseInstance' => TestErrorResponse::class,
            'assert' => $assertion2,
        ];
        yield [
            'query' => new TestQuery(),
            'responseData' => ['status' => false],
            'statusCode' => 500,
            'expectedResponseInstance' => TestErrorResponse::class,
            'assert' => $assertion2,
        ];

        yield [
            'query' => new SerializedNameQuery(),
            'responseData' => ['status' => true, 'foo_bar' => 'test'],
            'statusCode' => 200,
            'expectedResponseInstance' => SerializedNameResponse::class,
            'assert' => static fn (array $responseData, SerializedNameResponse $response): bool => $responseData['foo_bar'] === $response->renamed,
        ];
    }

    private function createMockedApiClient(MockResponse $mockResponse, bool $isAsync): ApiClientFactory
    {
        $mockHttpClient = new MockHttpClient($mockResponse);
        $responseFactory = new ResponseFactory($mockHttpClient);

        return new ApiClientFactory([new TestClient($isAsync)], new Client($responseFactory));
    }
}
