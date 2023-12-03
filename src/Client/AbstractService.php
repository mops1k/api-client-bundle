<?php

namespace ApiClientBundle\Client;

use Http\Client\Common\Plugin;

abstract class AbstractService implements ServiceInterface
{
    protected string $host;
    protected string $scheme;
    protected ?int $port = null;
    /**
     * @var array<Plugin>
     */
    protected array $plugins = [];

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

    public function getPlugins(): array
    {
        return $this->plugins;
    }
}
