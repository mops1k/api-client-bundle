<?php

namespace ApiClientBundle\Exceptions;

use JetBrains\PhpStorm\Pure;

final class ClientConfigurationNotFoundException extends \Exception
{
    #[Pure]
    public function __construct(string $className, int $code = 0, ?\Throwable $previous = null)
    {
        $message = \sprintf(
            'Client configuration %s not found.',
            $className
        );
        parent::__construct($message, $code, $previous);
    }
}
