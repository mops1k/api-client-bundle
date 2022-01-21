<?php

namespace ApiClientBundle\Tests;

use ApiClientBundle\Http\Client;
use ApiClientBundle\Http\ResponseFactory;
use ApiClientBundle\Service\Manager;
use ApiClientBundle\Tests\Configuration\TestClient;
use ApiClientBundle\Tests\Configuration\TestQuery;
use ApiClientBundle\Tests\Configuration\TestResponse;
use PHPUnit\Framework\TestCase;
use ProxyManager\Proxy\GhostObjectInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ApiClientTest extends TestCase
{
    /**
     * @dataProvider responseDataProvider
     *
     * @param array{status: bool} $responseData
     */
    public function testSendSyncRequest(array $responseData, int $statusCode): void
    {
        $mockResponse = new MockResponse(json_encode($responseData, JSON_THROW_ON_ERROR), ['http_code' => $statusCode]);

        $client = $this->createMockedApiClient($mockResponse, false)->use(TestClient::class);
        $query = new TestQuery();
        $response = $client->set($query);

        static::assertInstanceOf(TestResponse::class, $response);
        static::assertEquals($statusCode, $response->getStatusCode());
        static::assertEquals($responseData['status'], $response->getStatus());
    }

    /**
     * @dataProvider responseDataProvider
     *
     * @param array{status: bool} $responseData
     */
    public function testSendAsyncRequest(array $responseData, int $statusCode): void
    {
        $mockResponse = new MockResponse(json_encode($responseData, JSON_THROW_ON_ERROR), ['http_code' => $statusCode]);

        $client = $this->createMockedApiClient($mockResponse, true)->use(TestClient::class);
        $query = new TestQuery();
        $response = $client->set($query);

        static::assertInstanceOf(GhostObjectInterface::class, $response);
        static::assertFalse($response->isProxyInitialized());
        static::assertInstanceOf(TestResponse::class, $response);
        static::assertEquals($statusCode, $response->getStatusCode());
        static::assertEquals($responseData['status'], $response->getStatus());
        static::assertTrue($response->isProxyInitialized());
    }

    /**
     * @return iterable<array{responseData: array{status: bool}, statusCode: int}>
     */
    public function responseDataProvider(): iterable
    {
        yield ['responseData' => ['status' => true], 'statusCode' => 200];
        yield ['responseData' => ['status' => false], 'statusCode' => 400];
        yield ['responseData' => ['status' => false], 'statusCode' => 404];
        yield ['responseData' => ['status' => false], 'statusCode' => 500];
    }

    private function createMockedApiClient(MockResponse $mockResponse, bool $isAsync): Manager
    {
        $mockHttpClient = new MockHttpClient($mockResponse);
        $responseFactory = new ResponseFactory($mockHttpClient, null);

        return new Manager([new TestClient($isAsync)], new Client($responseFactory));
    }
}
