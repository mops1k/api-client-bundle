<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\QueryInterface;

final class QuerySerializationException extends \Exception
{
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
