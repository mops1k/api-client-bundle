<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\CollectionResponseInterface;
use ApiClientBundle\Interfaces\HeadersInterface;
use ApiClientBundle\Interfaces\StatusCodeInterface;

class GenericCollectionResponse extends ImmutableCollection implements CollectionResponseInterface, StatusCodeInterface, HeadersInterface
{
    /**
     * @var array<mixed>
     */
    protected array $headers = [];

    protected int $statusCode;

    /**
     * @return array<mixed>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
