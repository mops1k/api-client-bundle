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
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class HttpClient implements HttpClientInterface
{
    private RequestFactoryInterface $requestFactory;

    public function __construct(
        private readonly SerializerInterface $serializer,
        /**
         * @var \Traversable<Plugin>|null
         */
        protected readonly ?\Traversable $plugins = null,
        private ?ClientInterface $client = null,
    ) {
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
    }

    public function request(QueryInterface $query): ResponseInterface
    {
        $client = $this->getClient($query);

        $request = $this->requestFactory->createRequest(
            $query->getMethod()->value,
            RequestUriBuilder::build($query)
        );
        $body = RequestBodyBuilder::build($query);
        if (null !== $body) {
            $request = $request->withBody($body['stream']);
            if (null !== $body['boundary']) {
                $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary="' . $body['boundary'] . '"');
            }
        }

        foreach ($query->getHeaders() as $name => $value) {
            $request = $request->withHeader($name, $value);
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
        } catch (ClientExceptionInterface $e) {
            throw new HttpNetworkException(
                request: $request,
                previous: $e
            );
        }
    }

    private function getClient(QueryInterface $query): ClientInterface
    {
        if (!$this->client instanceof PluginClient) {
            $plugins = $this->plugins === null ? [] : \iterator_to_array($this->plugins);
            $servicePlugins = $query->getService()->getPlugins();
            $queryPlugins = $query->getPlugins();
            $this->mergePlugins($plugins, $servicePlugins);
            $this->mergePlugins($plugins, $queryPlugins);

            $this->client = new PluginClient(
                client: $this->client ?? Psr18ClientDiscovery::find(),
                plugins: $plugins,
            );
        }

        return $this->client;
    }

    /**
     * @param array<Plugin> $basePlugins
     * @param array<Plugin> $plugins
     */
    private function mergePlugins(array &$basePlugins, array $plugins): void
    {
        foreach ($basePlugins as $key => $basePlugin) {
            $basePluginName = $basePlugin::class;
            foreach ($plugins as $plugin) {
                if ($basePluginName === $plugin::class) {
                    $basePlugins[$key] = $plugin;

                    break;
                }

                $basePlugins[] = $plugin;
            }
        }
    }
}
