<?php

namespace ApiClientBundle\Exceptions;

final class ResponseClassNotFoundException extends \Exception
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
