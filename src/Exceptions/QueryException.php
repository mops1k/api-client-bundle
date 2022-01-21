<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\QueryInterface;

final class QueryException extends \Exception
{
    /**
     * @param QueryInterface<object> $query
     */
    public function __construct(QueryInterface $query, int $code = 0, ?\Throwable $previous = null)
    {
        $message = \sprintf(
            'Query %s execute has fail.',
            $query::class
        );
        parent::__construct($message, $code, $previous);
    }
}
