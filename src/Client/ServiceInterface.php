<?php

namespace ApiClientBundle\Client;

interface ServiceInterface
{
    public function getHost(): string;

    public function getPort(): int;

    public function getScheme(): string;
}
