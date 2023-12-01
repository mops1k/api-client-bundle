<?php

namespace ApiClientBundle\Tests;

use ApiClientBundle\ApiClientBundle;
use ApiClientBundle\Client\ResponseInterface;
use ApiClientBundle\HTTP\HttpClient;
use ApiClientBundle\Tests\Mock\Kernel;
use ApiClientBundle\Tests\Mock\Query;
use Doctrine\Common\Annotations\AnnotationReader;
use Http\Discovery\Psr17Factory;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Http\Message\RequestMatcher\RequestMatcher;
use Http\Mock\Client as MockHttpClient;
use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ClientTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testHttpClientRequest(): void
    {
        // needed for correctly reading name-converting annotations
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $serializer = new Serializer(
            [new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter)],
            ['json' => new JsonEncoder()]
        );

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

        $client = new HttpClient(serializer: $serializer, client: $mockHttpClient, plugins: []);

        /** @var Mock\Response $response */
        $response = $client->request(new Query());
        self::assertEquals('Ok!', $response->status);
    }
}
