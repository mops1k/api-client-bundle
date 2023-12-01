<?php

namespace ApiClientBundle\Client;

abstract class AbstractService implements ServiceInterface
{
    protected string $host;
    protected string $scheme;
    /**
     * @var array<mixed>
     */
    protected array $defaultOptions = [];
    protected int $port = 80;

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }
}
