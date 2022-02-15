<?php

namespace ApiClientBundle\Tests;

use ApiClientBundle\Http\Client;
use ApiClientBundle\Http\ResponseFactory;
use ApiClientBundle\Model\AbstractQuery;
use ApiClientBundle\Model\AbstractResponse;
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

/**
 * @phpstan-type TAssertCallable callable(array<mixed>,object):bool
 */
class ApiClientTest extends KernelTestCase
{
    /**
     * @dataProvider responseDataProvider
     *
     * @param class-string<AbstractQuery<object>> $queryClass
     * @param array<mixed>|string $responseData
     * @param class-string<AbstractResponse> $expectedResponseInstance
     * @param TAssertCallable|null $responseAssertion
     */
    public function testSendSyncRequest(
        string $queryClass,
        array|string $responseData,
        int $statusCode,
        string $expectedResponseInstance,
        ?callable $responseAssertion = null,
    ): void {
        $mockResponse = new MockResponse(json_encode($responseData, JSON_THROW_ON_ERROR), ['http_code' => $statusCode]);

        $client = $this->createMockedApiClient($mockResponse, false)->use(TestClient::class);
        $query = new $queryClass();
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
     * @param class-string<AbstractQuery<object>> $queryClass
     * @param array<mixed>|string $responseData
     * @param class-string<AbstractResponse> $expectedResponseInstance
     * @param TAssertCallable|null $responseAssertion
     */
    public function testSendAsyncRequest(
        string $queryClass,
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
        static::assertInstanceOf(TestResponse::class, $response);
        static::assertEquals($statusCode, $response->getStatusCode());
        // if (null !== $responseAssertion) {
        // todo: ответы с ошибками заворачиваются в TestResponse всё равно, это неправильно
        // static::assertTrue($responseAssertion($responseData, $response));
        // }
        if (isset($responseData['status'])) {
            static::assertEquals($responseData['status'], $response->getStatus());
        }
        static::assertTrue($response->isProxyInitialized());
        // todo: проверить что в $response нужный класс ответа
        // static::assertInstanceOf($expectedResponseInstance, $response);
    }

    /**
     * @return iterable<array{
     *     query: class-string<AbstractQuery>,
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
            'query' => TestQuery::class,
            'responseData' => ['status' => false],
            'statusCode' => 500,
            'expectedResponseInstance' => TestErrorResponse::class,
            'assert' => static fn (array $responseData, TestErrorResponse $response): bool => $response->getRawContent() === \json_encode(
                $responseData,
                JSON_THROW_ON_ERROR
            ),
        ];

        yield [
            'query' => TestQuery::class,
            'responseData' => ['status' => true],
            'statusCode' => 200,
            'expectedResponseInstance' => TestResponse::class,
            'assert' => $assertion1,
        ];
        yield [
            'query' => TestQuery::class,
            'responseData' => ['status' => false],
            'statusCode' => 400,
            'expectedResponseInstance' => TestErrorResponse::class,
            'assert' => $assertion2,
        ];
        yield [
            'query' => TestQuery::class,
            'responseData' => ['status' => false],
            'statusCode' => 404,
            'expectedResponseInstance' => TestErrorResponse::class,
            'assert' => $assertion2,
        ];
        yield [
            'query' => TestQuery::class,
            'responseData' => ['status' => false],
            'statusCode' => 500,
            'expectedResponseInstance' => TestErrorResponse::class,
            'assert' => $assertion2,
        ];

        yield [
            'query' => SerializedNameQuery::class,
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
