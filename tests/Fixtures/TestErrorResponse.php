<?php

namespace ApiClientBundle\Tests\Fixtures;

use ApiClientBundle\Model\GenericErrorResponse;

class TestErrorResponse extends GenericErrorResponse
{
    protected bool $status;

    public function getStatus(): bool
    {
        return $this->status;
    }
}
