<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\ApiClientExceptionInterface;
use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Model\GenericErrorResponse;

final class ClientNotSupportedByQueryException extends \Exception implements ApiClientExceptionInterface
{
    /**
     * @param QueryInterface<object, GenericErrorResponse> $query
     */
    public function __construct(
        ClientConfigurationInterface $clientConfiguration,
        QueryInterface $query,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $message = \sprintf(
            'Client %s not supported by query %s',
            $clientConfiguration::class,
            $query::class
        );
        parent::__construct($message, $code, $previous);
    }
}
