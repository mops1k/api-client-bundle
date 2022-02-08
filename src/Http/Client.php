<?php

namespace ApiClientBundle\Http;

use ApiClientBundle\Exceptions\ClientNotSupportedByQueryException;
use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use ApiClientBundle\Interfaces\ClientInterface;
use ApiClientBundle\Interfaces\QueryInterface;

final class Client implements ClientInterface
{
    private ClientConfigurationInterface $configuration;

    public function __construct(private ResponseFactory $responseFactory)
    {
    }

    public function setConfiguration(ClientConfigurationInterface $clientConfiguration): ClientInterface
    {
        $this->configuration = $clientConfiguration;

        return $this;
    }

    public function request(QueryInterface $queryConfiguration): object
    {
        if (!$queryConfiguration->support($this->getConfiguration())) {
            throw new ClientNotSupportedByQueryException($this->getConfiguration(), $queryConfiguration);
        }

        return $this->responseFactory->execute($this, $queryConfiguration);
    }

    public function getConfiguration(): ClientConfigurationInterface
    {
        return $this->configuration;
    }
}
