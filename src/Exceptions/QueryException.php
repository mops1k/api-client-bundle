<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\ApiClientExceptionInterface;
use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Model\GenericErrorResponse;

final class QueryException extends \RuntimeException implements ApiClientExceptionInterface
{
    /**
     * @param QueryInterface<object, GenericErrorResponse> $query
     */
    public function __construct(QueryInterface $query, int $code = 0, ?\Throwable $previous = null)
    {
        $message = \sprintf(
            'Query %s execution failed.',
            $query::class
        );
        parent::__construct($message, $code, $previous);
    }
}
