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
     */
    public function testSendSyncRequest(array $responseData, int $statusCode): void
    {
        $mockResponse = new MockResponse(json_encode($responseData, JSON_THROW_ON_ERROR), ['http_code' => $statusCode]);

        $client = $this->createMockedApiClient($mockResponse, false)->use(TestClient::class);
        $query = new TestQuery();
        $response = $client->set($query);

        $this->assertInstanceOf(TestResponse::class, $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals($responseData['status'], $response->getStatus());
    }

    /**
     * @dataProvider responseDataProvider
     */
    public function testSendAsyncRequest(array $responseData, int $statusCode): void
    {
        $mockResponse = new MockResponse(json_encode($responseData, JSON_THROW_ON_ERROR), ['http_code' => $statusCode]);

        $client = $this->createMockedApiClient($mockResponse, true)->use(TestClient::class);
        $query = new TestQuery();
        $response = $client->set($query);

        $this->assertInstanceOf(GhostObjectInterface::class, $response);
        $this->assertFalse($response->isProxyInitialized());
        $this->assertInstanceOf(TestResponse::class, $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals($responseData['status'], $response->getStatus());
        $this->assertTrue($response->isProxyInitialized());
    }

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

        return new Manager([new TestClient($isAsync),], new Client($responseFactory));
    }
}
