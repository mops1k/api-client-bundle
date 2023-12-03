<?php

namespace ApiClientBundle\Exception;

use ApiClientBundle\Enum\HttpResponseStatusEnum;
use Http\Client\Exception\HttpException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ServerErrorException extends HttpException
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        \Exception $previous = null,
    ) {
        $httpStatusEnum = HttpResponseStatusEnum::tryFromCode($response->getStatusCode());

        parent::__construct($httpStatusEnum->value, $request, $response, $previous);
    }
}
