<?php

namespace ApiClientBundle\Http;

use ApiClientBundle\Exceptions\ErrorResponseException;
use ApiClientBundle\Exceptions\QueryException;
use ApiClientBundle\Exceptions\QuerySerializationException;
use ApiClientBundle\Exceptions\ResponseClassNotFoundException;
use ApiClientBundle\Interfaces\ClientInterface;
use ApiClientBundle\Interfaces\GenericErrorResponseInterface;
use ApiClientBundle\Interfaces\HeadersInterface;
use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Interfaces\StatusCodeInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Serializer\Encoder\ChainEncoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterfaceAlias;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ResponseFactory
{
    /**
     * @var array<class-string<ClientInterface>, HttpClientInterface>
     */
    private array $initializedClients = [];

    private Serializer $serializer;

    public function __construct(private HttpClientInterface $httpClient)
    {
        // Чтобы быть уверенными в том, что у нас сериализатор будет запускать в правильном порядке нормализаторы,
        // а также иметь уверенность, что все поддерживаемые форматы сериализатора включены и аттрибуты с аннотациями
        // читаются, независимо от конфигурации сериализатора в проекте
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $this->serializer = new Serializer(
            [
                new PropertyNormalizer($classMetadataFactory, $metadataAwareNameConverter),
                new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter),
            ],
            [
                new JsonEncoder(),
                new XmlEncoder(),
                new YamlEncoder(),
                new CsvEncoder(),
                new ChainEncoder(),
            ]
        );
    }

    /**
     * @template TResponse of object
     * @template TErrorResponse of GenericErrorResponse
     *
     * @param QueryInterface<TResponse, TErrorResponse> $query
     *
     * @throws QueryException
     * @throws QuerySerializationException
     *
     * @return TResponse|TErrorResponse
     */
    public function execute(ClientInterface $client, QueryInterface $query): object
    {
        return match ($client->getConfiguration()->isAsync()) {
            // todo: вынести async вариант в отдельный класс
            true => $this->makeAsyncRequest($client, $query),
            false => $this->makeRequest($client, $query),
        };
    }

    /**
     * @template TResponse of object
     * @template TErrorResponse of GenericErrorResponse
     *
     * @param QueryInterface<TResponse, TErrorResponse> $query
     *
     * @throws QueryException
     * @throws QuerySerializationException
     *
     * @return TResponse|TErrorResponse
     */
    public function makeRequest(ClientInterface $client, QueryInterface $query): object
    {
        try {
            $responseClassName = $query->responseClassName();
            if (!class_exists($responseClassName)) {
                throw new ResponseClassNotFoundException($responseClassName);
            }

            $httpClient = $this->initializeClient($client);

            $response = $httpClient->request(
                $query->method(),
                $query->path(),
                $this->normalizeOptions($query)
            );

            $content = $response->getContent(false);

            if ($response->getStatusCode() >= 400) {
                if (!is_a($query->errorResponseClassName(), GenericErrorResponseInterface::class, true)) {
                    throw new ErrorResponseException($query);
                }
                $data = [
                    'rawContent' => $content,
                    'statusCode' => $response->getStatusCode(),
                    'headers' => $response->getHeaders(false),
                ];

                $object = $this->serializer->deserialize(
                    $content,
                    $query->errorResponseClassName(),
                    $query->serializerResponseFormat()
                );

                $this->serializer->denormalize(
                    $data,
                    $object::class,
                    null,
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $object]
                );

                return $object;
            }

            $object = $this->serializer->deserialize(
                $content,
                $responseClassName,
                $query->serializerResponseFormat()
            );
            assert(is_a($object, $responseClassName, false));

            $this->addAdditionalData($response, $query, $object);
        } catch (HttpClientExceptionInterface|DecodingExceptionInterface $exception) {
            throw new QueryException($query, $exception->getCode(), $exception);
        } catch (SerializerExceptionInterfaceAlias $exception) {
            throw new QuerySerializationException($query, $exception->getCode(), $exception);
        }

        return $object;
    }

    /**
     * @template TResponse of object
     * @template TErrorResponse of GenericErrorResponse
     *
     * @param QueryInterface<TResponse, TErrorResponse> $query
     *
     * @throws ResponseClassNotFoundException
     *
     * @return TResponse|TErrorResponse
     */
    private function makeAsyncRequest(ClientInterface $client, QueryInterface $query): object
    {
        if (!class_exists($query->responseClassName())) {
            throw new ResponseClassNotFoundException($query->responseClassName());
        }

        $lazyLoadingFactory = new LazyLoadingGhostFactory();
        $httpClient = $this->initializeClient($client);

        $initializer = function (
            GhostObjectInterface $ghostObject,
            string $method,
            array $parameters,
            &$initializer,
            array $properties,
        ) use ($httpClient, $query) {
            $initializer = null;

            try {
                $response = $httpClient->request(
                    $query->method(),
                    $query->path(),
                    $this->normalizeOptions($query)
                );

                $this->serializer->deserialize(
                    $response->getContent(false),
                    $ghostObject::class,
                    $query->serializerResponseFormat(),
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $ghostObject]
                );

                $this->addAdditionalData($response, $query, $ghostObject);
                // @todo: заворачивать ответы с кодом <=400 в своё исключение (чтобы можно было получить тело ответа из него)
            } catch (TransportException|DecodingExceptionInterface $exception) {
                throw new QueryException($query, $exception->getCode(), $exception);
            } catch (SerializerExceptionInterfaceAlias $exception) {
                throw new QuerySerializationException($query, $exception->getCode(), $exception);
            }

            return true;
        };

        return $lazyLoadingFactory->createProxy($query->responseClassName(), $initializer);
    }

    /**
     * @template TResponse of object
     * @template TErrorResponse of GenericErrorResponse
     *
     * @param QueryInterface<TResponse, TErrorResponse> $query
     *
     * @return array<string, mixed>
     */
    private function normalizeOptions(QueryInterface $query): array
    {
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

        return $options;
    }

    /**
     * @template TResponse of object
     * @template TErrorResponse of GenericErrorResponse
     *
     * @param QueryInterface<TResponse, TErrorResponse> $query
     *
     * @throws TransportExceptionInterface
     */
    private function addAdditionalData(ResponseInterface $response, QueryInterface $query, ?object $object): void
    {
        if (!$object) {
            return;
        }

        $additionalData = [];
        if ($object instanceof StatusCodeInterface) {
            $additionalData['statusCode'] = $response->getStatusCode();
        }

        if ($object instanceof HeadersInterface) {
            $additionalData['headers'] = $response->getHeaders(false);
        }

        if (!$additionalData) {
            return;
        }

        $this->serializer->denormalize(
            $additionalData,
            $query->responseClassName(),
            null,
            [AbstractNormalizer::OBJECT_TO_POPULATE => $object]
        );
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
            'headers' => $client->getConfiguration()->headers()->all(),
        ];
        $options = \array_merge_recursive($options, $client->getConfiguration()->options()->all());
        $this->initializedClients[$client->getConfiguration()::class] = $this->httpClient->withOptions($options);

        return $this->initializedClients[$client->getConfiguration()::class];
    }
}
