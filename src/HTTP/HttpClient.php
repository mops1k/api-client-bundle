<?php

namespace ApiClientBundle\HTTP;

use ApiClientBundle\Builder\RequestBodyBuilder;
use ApiClientBundle\Builder\RequestUriBuilder;
use ApiClientBundle\Client\QueryInterface;
use ApiClientBundle\Client\ResponseInterface;
use ApiClientBundle\Exception\HttpRequestException;
use ApiClientBundle\Exception\ServerErrorException;
use Http\Client\Common\Exception\ClientErrorException;
use Http\Client\Common\Exception\ServerErrorException as BaseServerErrorException;
use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class HttpClient
{
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        private readonly SerializerInterface $serializer,
        /**
         * @var \Traversable<Plugin>|null
         */
        protected readonly ?\Traversable $plugins = null,
        private ?ClientInterface $client = null,
    ) {
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    }

    public function request(QueryInterface $query): ResponseInterface
    {
        $client = $this->getClient();

        $request = $this->requestFactory->createRequest(
            $query->getMethod()->value,
            RequestUriBuilder::build($query)
        );
        $body = RequestBodyBuilder::build($query);
        if (null !== $body) {
            $request->withBody($this->streamFactory->createStream($body));
        }

        foreach ($query->getHeaders() as $name => $value) {
            $request->withHeader($name, $value);
        }

        try {
            $psr17Response = $client->sendRequest($request);

            return $this->serializer->deserialize(
                $psr17Response->getBody()->getContents(),
                $query->getResponse(),
                $query->getFormat()
            );
        } catch (ClientErrorException $e) {
            if ($e->getResponse()->getStatusCode() >= 400) {
                throw new HttpRequestException(
                    request: $e->getRequest(),
                    response: $e->getResponse(),
                    previous: $e
                );
            }

            throw new HttpClientException(
                request: $e->getRequest(),
                response: $e->getResponse(),
                previous: $e
            );
        } catch (BaseServerErrorException $e) {
            throw new ServerErrorException(
                request: $e->getRequest(),
                response: $e->getResponse(),
                previous: $e
            );
        }
    }

    private function getClient(): ClientInterface
    {
        if (!$this->client instanceof PluginClient) {
            $this->client = new PluginClient(
                client: $this->client ?? Psr18ClientDiscovery::find(),
                plugins: $this->plugins === null ? [] : \iterator_to_array($this->plugins),
            );
        }

        return $this->client;
    }
}
