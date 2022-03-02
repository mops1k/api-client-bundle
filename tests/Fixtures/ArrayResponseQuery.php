<?php

declare(strict_types=1);

namespace ApiClientBundle\Tests\Fixtures;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use ApiClientBundle\Model\AbstractQueryWithGenericErrorResponse;

/**
 * @extends AbstractQueryWithGenericErrorResponse<ArrayResponseUsingPhpDoc|ArrayResponseUsingMethod>
 */
class ArrayResponseQuery extends AbstractQueryWithGenericErrorResponse
{
    public function __construct(private string $responseClass)
    {
        parent::__construct();
    }

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
        return $this->responseClass;
    }
}
