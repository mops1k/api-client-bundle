<?php

namespace ApiClientBundle\Service;

use ApiClientBundle\Exceptions\ClientConfigurationNotFoundException;
use ApiClientBundle\Exceptions\ClientConfigurationNotSupportedException;
use ApiClientBundle\Http\Client;
use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use ApiClientBundle\Interfaces\ClientInterface;

final class Manager
{
    /**
     * @var array<class-string<ClientConfigurationInterface>, ClientConfigurationInterface>
     */
    private array $clients = [];

    /**
     * @throws ClientConfigurationNotSupportedException
     */
    public function __construct(?iterable $clients, private Client $httClient)
    {
        if (!$clients) {
            return;
        }

        foreach ($clients as $client) {
            if (!$client instanceof ClientConfigurationInterface) {
                throw new ClientConfigurationNotSupportedException($client);
            }

            $this->clients[$client::class] = $client;
        }
    }

    /**
     * @throws ClientConfigurationNotFoundException
     */
    public function use(string $className): ClientInterface
    {
        if (!array_key_exists($className, $this->clients)) {
            throw new ClientConfigurationNotFoundException($className);
        }

        return (clone $this->httClient)->setConfiguration($this->clients[$className]);
    }
}
