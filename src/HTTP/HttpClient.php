<?php

namespace ApiClientBundle\HTTP;

use ApiClientBundle\Builder\RequestBodyBuilder;
use ApiClientBundle\Builder\RequestUriBuilder;
use ApiClientBundle\Client\QueryInterface;
use ApiClientBundle\Client\ResponseInterface;
use ApiClientBundle\Client\ServiceInterface;
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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class HttpClient implements HttpClientInterface
{
    private RequestFactoryInterface $requestFactory;

    public function __construct(
        private readonly SerializerInterface $serializer,
        protected ContainerInterface $container,
        /**
         * @var \Traversable<Plugin>|null
         */
        protected readonly ?\Traversable $plugins = null,
        private ?ClientInterface $client = null,
        protected RequestBodyBuilder $bodyBuilder = new RequestBodyBuilder(),
    ) {
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * @throws HttpClientException
     * @throws HttpRequestException
     * @throws ServerErrorException
     * @throws HttpNetworkException
     */
    public function request(QueryInterface $query): ResponseInterface
    {
        $client = $this->getClient($query);
        $service = $this->getService($query);

        $request = $this->requestFactory->createRequest(
            $query->getMethod()->value,
            RequestUriBuilder::build($query, $service)
        );

        $body = RequestBodyBuilder::build($query, $service);
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
        $service = $this->getService($query);
        if (!$this->client instanceof PluginClient) {
            $plugins = $this->plugins === null ? [] : \iterator_to_array($this->plugins);
            $servicePlugins = $service->getPlugins();
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
        // Compute search index
        $existingPlugins = [];
        foreach ($basePlugins as $key => $basePlugin) {
            $existingPlugins[$basePlugin::class] = $key;
        }

        foreach ($plugins as $plugin) {
            if (\array_key_exists($plugin::class, $existingPlugins)) {
                $basePlugins[$existingPlugins[$plugin::class]] = $plugin;

                continue;
            }

            $basePlugins[] = $plugin;
        }
    }

    private function getService(QueryInterface $query): ServiceInterface
    {
        $serviceClass = $query->getService();
        if (!$this->container->has($serviceClass)) {
            return new $serviceClass();
        }

        return $this->container->get($serviceClass);
    }
}
