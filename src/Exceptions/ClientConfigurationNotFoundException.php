<?php

namespace ApiClientBundle\Exceptions;

final class ClientConfigurationNotFoundException extends \Exception
{
    public function __construct(string $className, int $code = 0, ?\Throwable $previous = null)
    {
        $message = \sprintf(
            'Client configuration %s not found.',
            $className
        );
        parent::__construct($message, $code, $previous);
    }
}
