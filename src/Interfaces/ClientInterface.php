<?php

namespace ApiClientBundle\Interfaces;

interface ClientInterface
{
    public function getConfiguration(): ClientConfigurationInterface;
    public function setConfiguration(ClientConfigurationInterface $clientConfiguration): self;

    /**
     * @template TResponse of object
     *
     * @param QueryInterface<TResponse> $queryConfiguration
     *
     * @return TResponse
     */
    public function set(QueryInterface $queryConfiguration): object;
}
