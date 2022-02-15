<?php

declare(strict_types=1);

namespace ApiClientBundle\Tests\Fixtures;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use ApiClientBundle\Model\AbstractQuery;

/**
 * @extends AbstractQuery<SerializedNameResponse, TestErrorResponse>
 */
class SerializedNameQuery extends AbstractQuery
{
    public function path(): string
    {
        return '/foo';
    }

    public function support(ClientConfigurationInterface $clientConfiguration): bool
    {
        return $clientConfiguration instanceof TestClient;
    }

    public function responseClassName(): string
    {
        return SerializedNameResponse::class;
    }

    public function errorResponseClassName(): string
    {
        return TestErrorResponse::class;
    }
}
