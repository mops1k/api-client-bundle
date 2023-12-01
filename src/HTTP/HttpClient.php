<?php

namespace ApiClientBundle\HTTP;

use ApiClientBundle\Builder\RequestBodyBuilder;
use ApiClientBundle\Builder\RequestUriBuilder;
use ApiClientBundle\Client\QueryInterface;
use ApiClientBundle\Client\ResponseInterface;
use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
class HttpClient
{
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    /**
     * @param iterable<Plugin> $plugins
     */
    public function __construct(
        private readonly SerializerInterface $serializer,
        iterable $plugins,
        ?ClientInterface $client = null
    ) {
        $this->client = new PluginClient(
            client: $client ?? Psr18ClientDiscovery::find(),
            plugins: $plugins
        );
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    }

    public function request(QueryInterface $query): ResponseInterface
    {
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

        // TODO: implement curl options
        $defaultOptions = $query->getService()->getDefaultOptions();
        $options = array_merge_recursive($defaultOptions, $query->getOptions());

        try {
            $psr17Response = $this->client->sendRequest($request);

            return $this->serializer->deserialize(
                $psr17Response->getBody()->getContents(),
                $query->getResponse(),
                $query->getFormat()
            );
        } catch (ClientExceptionInterface $e) {
            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
