<?php

namespace ApiClientBundle\Tests\Configuration;

use ApiClientBundle\Model\AbstractClientConfiguration;

class TestClient extends AbstractClientConfiguration
{
    public function __construct(private bool $isAsync = false)
    {
        parent::__construct();
    }

    public function domain(): string
    {
        return 'test.com';
    }

    public function scheme(): string
    {
        return self::SCHEME_HTTP;
    }

    public function isAsync(): bool
    {
        return $this->isAsync;
    }
}
