<?php

namespace ApiClientBundle\Client;

use Http\Client\Common\Plugin;

interface ServiceInterface
{
    public function getHost(): string;

    public function getPort(): ?int;

    public function getScheme(): string;

    /**
     * @return array<Plugin>
     */
    public function getPlugins(): array;
}
