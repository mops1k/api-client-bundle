<?php

namespace ApiClientBundle\Interfaces;

interface ClientInterface
{
    public function getConfiguration(): ClientConfigurationInterface;
    public function setConfiguration(ClientConfigurationInterface $clientConfiguration): self;
    public function set(QueryInterface $queryConfiguration);
}
