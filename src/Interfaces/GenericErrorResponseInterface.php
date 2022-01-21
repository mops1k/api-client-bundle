<?php

namespace ApiClientBundle\Interfaces;

interface GenericErrorResponseInterface extends StatusCodeInterface, HeadersInterface
{
    public function getRawContent(): string;
}
