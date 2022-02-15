<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\HeadersInterface;
use ApiClientBundle\Interfaces\StatusCodeInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractResponse implements StatusCodeInterface, HeadersInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $headers = [];

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
