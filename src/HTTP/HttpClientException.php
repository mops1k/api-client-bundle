<?php

namespace ApiClientBundle\HTTP;

use Http\Client\Exception\HttpException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpClientException extends HttpException
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        \Exception $previous = null,
    ) {
        parent::__construct('Http client error!', $request, $response, $previous);
    }
}
