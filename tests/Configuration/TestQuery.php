<?php

namespace ApiClientBundle\Tests\Configuration;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use ApiClientBundle\Model\AbstractQuery;

/**
 * @extends AbstractQuery<TestResponse, TestErrorResponse>
 */
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

    public function errorResponseClassName(): string
    {
        return TestErrorResponse::class;
    }
}
