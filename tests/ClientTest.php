<?php

namespace ApiClientBundle\Tests;

use ApiClientBundle\HTTP\HttpClient;
use ApiClientBundle\Tests\Mock\Kernel;
use ApiClientBundle\Tests\Mock\Query;
use Doctrine\Common\Annotations\AnnotationReader;
use GuzzleHttp\Psr7\Response;
use Http\Discovery\Psr17Factory;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Http\Message\RequestMatcher\RequestMatcher;
use Http\Mock\Client as MockHttpClient;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
        $mockResponse = $this->createMock(Response::class);

        $requestMatcher = new RequestMatcher();
        $mockHttpClient->on($requestMatcher, function (RequestInterface $request) use ($mockResponseContents, $mockResponse) {
            $mockResponse->method('getStatusCode')->willReturn(200);
            $streamMock = (new Psr17Factory())->createStream($mockResponseContents);
            $mockResponse->method('getBody')->willReturn($streamMock);

            return $mockResponse;
        });

        $client = self::getContainer()->get(HttpClient::class);

        $reflectionProperty = new \ReflectionProperty($client, 'client');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($client, $mockHttpClient);

        /** @var Mock\Response $response */
        $response = $client->request(new Query());
        self::assertEquals('Ok!', $response->status);
    }
}
