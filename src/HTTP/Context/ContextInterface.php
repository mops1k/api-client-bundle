<?php

namespace ApiClientBundle\HTTP\Context;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ContextInterface
{
    public function __construct(RequestInterface $request, ?ResponseInterface $response);

    public function getRequest(): RequestInterface;
    public function getResponse(): ?ResponseInterface;
}
