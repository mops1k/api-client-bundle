<?php

namespace ApiClientBundle\Exception;

interface HttpExceptionInterface extends \Throwable
{
    public function getContent(): string;
}
