<?php

namespace ApiClientBundle\Tests\Fixtures;

use ApiClientBundle\Model\AbstractResponse;

class TestResponse extends AbstractResponse
{
    protected bool $status;

    public function getStatus(): bool
    {
        return $this->status;
    }
}
