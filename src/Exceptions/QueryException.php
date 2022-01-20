<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\QueryInterface;
use JetBrains\PhpStorm\Pure;

final class QueryException extends \Exception
{
    #[Pure]
    public function __construct(QueryInterface $query, int $code = 0, ?\Throwable $previous = null)
    {
        $message = \sprintf(
            'Query %s execute has fail.',
            $query::class
        );
        parent::__construct($message, $code, $previous);
    }
}
