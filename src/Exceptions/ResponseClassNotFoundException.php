<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\ApiClientExceptionInterface;

final class ResponseClassNotFoundException extends \Exception implements ApiClientExceptionInterface
{
    public function __construct(string $objectName, int $code = 0, ?\Throwable $previous = null)
    {
        $message = \sprintf(
            'Response %s not found.',
            $objectName
        );
        parent::__construct($message, $code, $previous);
    }
}
