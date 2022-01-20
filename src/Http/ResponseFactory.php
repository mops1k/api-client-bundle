<?php

namespace ApiClientBundle\Http;

use ApiClientBundle\Interfaces\ClientInterface;
use ApiClientBundle\Interfaces\HeadersInterface;
use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Interfaces\StatusCodeInterface;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;
use Symfony\Component\Serializer\Encoder\ChainEncoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ResponseFactory
{
    /**
     * @var array<class-string<ClientInterface>, HttpClientInterface>
     */
    private array $initializedClients = [];

    private SerializerInterface $serializer;

    public function __construct(private HttpClientInterface $httpClient)
    {
        $this->serializer = new Serializer(
            [
                new PropertyNormalizer(),
                new DateTimeNormalizer(),
            ],
            [
                new XmlEncoder(),
                new JsonEncoder(),
                new CsvEncoder(),
                new YamlEncoder(),
                new ChainEncoder(),
            ]
        );
    }

    public function execute(ClientInterface $client, QueryInterface $query): object
    {
        $lazyLoadingFactory = new LazyLoadingGhostFactory();
        $httpClient = $this->initializeClient($client);

        $initializer = function(
            GhostObjectInterface $ghostObject,
            string $method,
            array $parameters,
            &$initializer,
            array $properties
        ) use ($httpClient, $query) {
            $initializer = null;
            $options = \array_merge_recursive(
                $query->options()->all(),
                ['query' => $query->queryData()->all()],
                ['headers' => $query->headers()->all()],
                ['json' => $query->jsonData()->all()],
                ['body' => $query->formData()->all()],
            );
            switch (true) {
                case $options['json'] === []:
                    unset($options['json']);
                    break;
                case $options['body'] === []:
                    unset($options['body']);
                    break;
            }
            $response = $httpClient->request(
                $query->method(),
                $query->path(),
                $options
            );

            $this->serializer->deserialize(
                $response->getContent(false),
                $ghostObject::class,
                $query->serializerResponseFormat(),
                [AbstractNormalizer::OBJECT_TO_POPULATE => $ghostObject]
            );

            if ($ghostObject instanceof StatusCodeInterface) {
                $properties["\0*\0statusCode"] = $response->getStatusCode();
            }

            if ($ghostObject instanceof HeadersInterface) {
                $properties["\0*\0headers"] = $response->getHeaders();
            }

            return true;
        };

        return $lazyLoadingFactory->createProxy($query->responseClassName(), $initializer);
    }

    private function initializeClient(ClientInterface $client): HttpClientInterface
    {
        if (isset($this->initializedClients[$client->getConfiguration()::class])) {
            return $this->initializedClients[$client->getConfiguration()::class];
        }

        $options = [
            'base_uri' => \sprintf(
                '%s://%s',
                $client->getConfiguration()->scheme(),
                $client->getConfiguration()->domain()
            ),
            'headers' => $client->getConfiguration()->headers()->all()
        ];
        $options = \array_merge_recursive($options, $client->getConfiguration()->options()->all());
        $this->initializedClients[$client->getConfiguration()::class] = $this->httpClient->withOptions($options);

        return $this->initializedClients[$client->getConfiguration()::class];
    }
}
