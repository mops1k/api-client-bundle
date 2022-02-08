<?php

namespace ApiClientBundle\Interfaces;

use ApiClientBundle\Exceptions\QueryException;
use ApiClientBundle\Exceptions\QuerySerializationException;
use ApiClientBundle\Model\GenericErrorResponse;

interface ClientInterface
{
    /**
     * @internal
     */
    public function getConfiguration(): ClientConfigurationInterface;

    /**
     * @internal
     */
    public function setConfiguration(ClientConfigurationInterface $clientConfiguration): self;

    /**
     * @template TResponse of object
     * @template TErrorResponse of GenericErrorResponse
     *
     * @param QueryInterface<TResponse, TErrorResponse> $queryConfiguration
     *
     * @throws QueryException
     * @throws QuerySerializationException
     *
     * @return TResponse|TErrorResponse
     */
    public function request(QueryInterface $queryConfiguration): object;
}
