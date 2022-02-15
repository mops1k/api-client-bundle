<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\HeadersInterface;
use ApiClientBundle\Interfaces\StatusCodeInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractResponse implements StatusCodeInterface, HeadersInterface
{
    // todo: та же проблема с <public
    /**
     * @var array<string, mixed>
     */
    protected array $headers = [];

    // todo: та же проблема с <public
    protected int $statusCode = Response::HTTP_BAD_REQUEST;

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
