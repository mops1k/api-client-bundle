<?php

namespace ApiClientBundle\Tests\Mock;

use ApiClientBundle\Client\ResponseInterface;

class Response implements ResponseInterface
{
    public function __construct(public readonly string $status)
    {
    }
}
