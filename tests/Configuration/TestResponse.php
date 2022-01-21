<?php

namespace ApiClientBundle\Tests\Configuration;

use ApiClientBundle\Model\AbstractResponse;

class TestResponse extends AbstractResponse
{
    private bool $status;

    public function getStatus(): bool
    {
        return $this->status;
    }
}