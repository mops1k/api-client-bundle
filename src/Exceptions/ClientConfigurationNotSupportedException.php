<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;

final class ClientConfigurationNotSupportedException extends \Exception
{
    public function __construct(object $class, int $code = 0, ?\Throwable $previous = null)
    {
        $message = \sprintf(
            'Client configuration %s are not supported. Client class must implement %s',
            $class::class,
            ClientConfigurationInterface::class
        );
        parent::__construct($message, $code, $previous);
    }
}
