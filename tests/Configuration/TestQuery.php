<?php

namespace ApiClientBundle\Tests\Configuration;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use ApiClientBundle\Model\AbstractQuery;

class TestQuery extends AbstractQuery
{
    public function path(): string
    {
        return '/api';
    }

    public function support(ClientConfigurationInterface $clientConfiguration): bool
    {
        return $clientConfiguration instanceof TestClient;
    }

    public function responseClassName(): string
    {
        return TestResponse::class;
    }
}
