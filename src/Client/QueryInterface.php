<?php

namespace ApiClientBundle\Client;

use ApiClientBundle\Enum\HttpMethodEnum;

interface QueryInterface
{
    /**
     * @return ?string
     */
    public function getPath(): ?string;

    public function getMethod(): HttpMethodEnum;

    /**
     * @return array<string, mixed>
     */
    public function getQuery(): array;

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array;

    public function getBody(): ?string;


    /**
     * @return array<string, string|array<string>>
     */
    public function getHeaders(): array;

    public function getService(): ServiceInterface;

    /**
     * @return class-string<ResponseInterface>
     */
    public function getResponse(): string;

    public function getFormat(): string;
}
