<?php

namespace ApiClientBundle\Tests\Fixtures;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use ApiClientBundle\Model\AbstractQueryWithGenericErrorResponse;
use ApiClientBundle\Model\GenericCollectionResponse;

/**
 * @extends AbstractQueryWithGenericErrorResponse<GenericCollectionResponse>
 */
class CollectionResponseQuery extends AbstractQueryWithGenericErrorResponse
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
        return GenericCollectionResponse::class;
    }
}
