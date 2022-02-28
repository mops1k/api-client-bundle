<?php

namespace ApiClientBundle\Tests\Fixtures;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use ApiClientBundle\Model\AbstractQuery;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractQuery<TestResponse, TestErrorResponse>
 */
class TestQuery extends AbstractQuery
{
    public function __construct(private string $method = Request::METHOD_GET)
    {
        parent::__construct();
    }

    public function method(): string
    {
        return $this->method;
    }

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
