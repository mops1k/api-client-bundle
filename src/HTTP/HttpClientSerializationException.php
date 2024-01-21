<?php

namespace ApiClientBundle\HTTP;

use Http\Client\Exception\HttpException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientSerializationException extends HttpException
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        string $content,
        \Exception $previous = null,
    ) {
        parent::__construct(
            \sprintf('Http client serialization error for incoming data: "%s".', $content),
            $request,
            $response,
            $previous
        );
    }
}
