<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\GenericErrorResponseInterface;

class GenericErrorResponse extends AbstractResponse implements GenericErrorResponseInterface
{
    protected string $rawContent;

    public function getRawContent(): string
    {
        return $this->rawContent;
    }
}
