<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\ApiClientExceptionInterface;
use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Model\GenericErrorResponse;

class BadMethodException extends \Exception implements ApiClientExceptionInterface
{
    /**
     * @param QueryInterface<object, GenericErrorResponse> $query
     */
    public function __construct(QueryInterface $query, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf('Method "%s" unsupported for query %s, because query has form data sending.', $query->method(), $query::class), $code, $previous);
    }
}
