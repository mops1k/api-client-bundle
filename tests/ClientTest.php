<?php

namespace ApiClientBundle\Tests;

use ApiClientBundle\HTTP\HttpClient;
use ApiClientBundle\Tests\Mock\Kernel;
use ApiClientBundle\Tests\Mock\Query;
use ApiClientBundle\Tests\Mock\Response;
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
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testHttpClientRequest(): void
    {
        Psr18ClientDiscovery::prependStrategy(MockClientStrategy::class);
        $mockHttpClient = Psr18ClientDiscovery::find();
        self::assertInstanceOf(MockHttpClient::class, $mockHttpClient);

        $mockResponseContents = \file_get_contents(__DIR__ . '/_data/response/ok.json');
        $mockResponse = $this->createMock(HttpResponse::class);

        $requestMatcher = new RequestMatcher();
        $mockHttpClient->on($requestMatcher, function (RequestInterface $request) use ($mockResponseContents, $mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(200);
            $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        /** @var HttpClient $client */
        $client = self::getContainer()->get(HttpClient::class);

        $reflectionProperty = new \ReflectionProperty($client, 'client');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($client, $mockHttpClient);

        /** @var Response $response */
        $response = $client->request(new Query());
        self::assertEquals('Ok!', $response->status);
    }
}
