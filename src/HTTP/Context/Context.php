<?php

namespace ApiClientBundle\HTTP\Context;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Context implements ContextInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ?ResponseInterface $response
    ) {
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
