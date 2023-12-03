<?php

namespace ApiClientBundle\Client;

abstract class AbstractService implements ServiceInterface
{
    protected string $host;
    protected string $scheme;
    protected ?int $port = null;

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }
}
