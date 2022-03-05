<?php

namespace ApiClientBundle\Tests;

use ApiClientBundle\Exceptions\ClientConfigurationNotFoundException;
use ApiClientBundle\Exceptions\ClientConfigurationNotSupportedException;
use ApiClientBundle\Exceptions\ErrorResponseException;
use ApiClientBundle\Exceptions\QueryException;
use ApiClientBundle\Http\Client;
use ApiClientBundle\Http\ResponseFactory;
use ApiClientBundle\Interfaces\CollectionResponseInterface;
use ApiClientBundle\Interfaces\GenericErrorResponseInterface;
use ApiClientBundle\Interfaces\ImmutableCollectionInterface;
use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Model\AbstractResponse;
use ApiClientBundle\Model\GenericCollectionResponse;
use ApiClientBundle\Model\GenericErrorResponse;
use ApiClientBundle\Model\ImmutableCollection;
use ApiClientBundle\Service\ApiClientFactory;
use ApiClientBundle\Tests\Fixtures\ArrayResponseQuery;
use ApiClientBundle\Tests\Fixtures\ArrayResponseUsingMethod;
use ApiClientBundle\Tests\Fixtures\ArrayResponseUsingPhpDoc;
use ApiClientBundle\Tests\Fixtures\CollectionResponseQuery;
use ApiClientBundle\Tests\Fixtures\SerializedNameQuery;
use ApiClientBundle\Tests\Fixtures\SerializedNameResponse;
use ApiClientBundle\Tests\Fixtures\TestClient;
use ApiClientBundle\Tests\Fixtures\TestErrorResponse;
use ApiClientBundle\Tests\Fixtures\TestFile;
use ApiClientBundle\Tests\Fixtures\TestKernel;
use ApiClientBundle\Tests\Fixtures\TestQuery;
use ApiClientBundle\Tests\Fixtures\TestResponse;
use ProxyManager\Proxy\GhostObjectInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-type TAssertCallable callable(array<mixed>,object):(void|bool)
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
            static::assertThat(
                $responseAssertion($responseData, $response),
                static::logicalOr(
                    static::isNull(),
                    static::isTrue(),
                )
            );
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
        $response = $client->request($queryObject);
        static::assertInstanceOf(GhostObjectInterface::class, $response);
        static::assertFalse($response->isProxyInitialized());

        try {
            static::assertInstanceOf($queryObject->responseClassName(), $response);
            static::assertEquals($statusCode, $response->getStatusCode());
            if ($statusCode > 400) {
                $responseAssertion($responseData, $response);
                if (isset($responseData['status'])) {
                    static::assertEquals($responseData['status'], $response->getStatus());
                }
                $responseAssertion($responseData, $response);
            } else {
                static::assertEquals($statusCode, $response->getStatusCode());
                static::assertNotEquals($expectedResponseInstance, $response::class);
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

    public function testClientConfigurationNotFountException(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);

        $this->expectException(ClientConfigurationNotFoundException::class);
        $this->createMockedApiClient($mockResponse, true)->use('\\NotExistsConfiguration');
    }

    public function testClientConfigurationNotSupportedException(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);

        $this->expectException(ClientConfigurationNotSupportedException::class);
        /** @var ResponseFactory $responseFactory */
        $responseFactory = self::getContainer()->get(ResponseFactory::class);

        $mockHttpClient = new MockHttpClient($mockResponse);
        $responseFactory->setHttpClient($mockHttpClient);

        new ApiClientFactory([new TestQuery()], new Client($responseFactory));
    }

    /**
     * @dataProvider isSyncProvider
     */
    public function testFilesUploadingAsync(bool $isAsync): void
    {
        $mockResponse = new MockResponse(\json_encode(['status' => true]), ['http_code' => 200]);

        $client = $this->createMockedApiClient($mockResponse, $isAsync)->use(TestClient::class);
        $query = new TestQuery(Request::METHOD_POST);
        $query->files()->set('test_file', __DIR__ . '/Fixtures/stub.txt');
        $response = $client->request($query);

        if ($isAsync) {
            static::assertInstanceOf(GhostObjectInterface::class, $response);
            static::assertFalse($response->isProxyInitialized());
        }

        static::assertInstanceOf(TestResponse::class, $response);
        static::assertEquals(200, $response->getStatusCode());
        if ($isAsync) {
            /** @phpstan-ignore-next-line */
            static::assertTrue($response->isProxyInitialized());
        }
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
            'query' => new CollectionResponseQuery(),
            'responseData' => [['item' => 1], ['item' => 2], ['item' => 3]],
            'statusCode' => 200,
            'expectedResponseInstance' => GenericCollectionResponse::class,
            'assert' => static function(array $responseData, GenericCollectionResponse $response): bool {
                static::assertInstanceOf(ImmutableCollectionInterface::class, $response);
                static::assertFalse($response->isEmpty());
                static::assertCount(\count($responseData), $response);
                static::assertSame($responseData[0], $response->get(0));
                static::assertSame($responseData[1], $response->get(1));
                static::assertSame(0, $response->key());
                static::assertSame($responseData[0], $response->first());
                static::assertSame($responseData[2], $response->last());
                static::assertSame(2, $response->key());
                static::assertFalse($response->next());
                $response->rewind();
                static::assertSame($responseData[1], $response->current());
                static::assertTrue($response->contains(['item' => 2]));
                static::assertTrue($response->containsKey(2));
                static::assertFalse($response->containsKey(5));
                static::assertIsArray($response->toArray());
                static::assertSame([0, 1, 2], $response->getKeys());
                static::assertIsArray($response->getValues());

                $filteredResponse = $response->filter(fn($item) => $item['item'] !== 2);
                static::assertInstanceOf(ImmutableCollection::class, $filteredResponse);
                static::assertNotInstanceOf(CollectionResponseInterface::class, $filteredResponse);
                static::assertCount(2, $filteredResponse);

                return true;
            },
        ];

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

        foreach ([ArrayResponseUsingPhpDoc::class, ArrayResponseUsingMethod::class] as $responseClass) {
            yield [
                'query' => new ArrayResponseQuery($responseClass),
                'responseData' => ['files' => $files = [['name' => 'test'], ['name' => 'foo']]],
                'statusCode' => 200,
                'expectedResponseInstance' => $responseClass,
                'assert' => static function (array $responseData, ArrayResponseUsingPhpDoc|ArrayResponseUsingMethod $response) use ($files): void {
                    foreach ($response->files as $file) {
                        self::assertInstanceOf(TestFile::class, $file);
                        self::assertContains($file->name, array_column($files, 'name'));
                    }
                },
            ];
        }
    }

    /**
     * @return iterable<mixed>
     */
    public function isSyncProvider(): iterable
    {
        yield 'sync' => ['isAsync' => false];
        yield 'async' => ['isAsync' => true];
    }

    private function createMockedApiClient(MockResponse $mockResponse, bool $isAsync): ApiClientFactory
    {
        /** @var ResponseFactory $responseFactory */
        $responseFactory = self::getContainer()->get(ResponseFactory::class);

        $mockHttpClient = new MockHttpClient($mockResponse);
        $responseFactory->setHttpClient($mockHttpClient);

        return new ApiClientFactory([new TestClient($isAsync)], new Client($responseFactory));
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }
}
