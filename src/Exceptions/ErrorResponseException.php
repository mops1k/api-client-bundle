<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\ApiClientExceptionInterface;
use ApiClientBundle\Interfaces\GenericErrorResponseInterface;
use ApiClientBundle\Interfaces\QueryInterface;

class ErrorResponseException extends \Exception implements ApiClientExceptionInterface
{
    /**
     * @param QueryInterface<object> $query
     */
    public function __construct(QueryInterface $query, int $code = 0, ?\Throwable $previous = null)
    {
        $message = \sprintf(
            'Error response in query %s must implement %s',
            $query::class,
            GenericErrorResponseInterface::class
        );
        parent::__construct($message, $code, $previous);
    }
}
