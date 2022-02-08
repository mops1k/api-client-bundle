<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\ApiClientExceptionInterface;
use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Model\GenericErrorResponse;

final class QuerySerializationException extends \RuntimeException implements ApiClientExceptionInterface
{
    /**
     * @param QueryInterface<object, GenericErrorResponse> $query
     */
    public function __construct(QueryInterface $query, int $code = 0, ?\Throwable $previous = null)
    {
        $message = \sprintf(
            'Query %s response serialization error. Response object: %s.',
            $query::class,
            $query->responseClassName()
        );
        parent::__construct($message, $code, $previous);
    }
}
