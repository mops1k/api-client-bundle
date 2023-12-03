<?php

namespace ApiClientBundle\HTTP;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

class HttpNetworkException extends \Exception implements NetworkExceptionInterface
{
    public function __construct(private readonly RequestInterface $request, ?\Throwable $previous = null)
    {
        $message = $previous?->getMessage() ?? 'Network error.';
        parent::__construct($message, $previous?->getCode(), $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
