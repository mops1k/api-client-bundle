<?php

namespace ApiClientBundle\Client;

use ApiClientBundle\Enum\HttpMethodEnum;
use Http\Client\Common\Plugin;

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

    /**
     * Returns array of file paths or single file path.
     *
     * @return array<string, string|array<string>>|null
     */
    public function getFiles(): null|array;

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

    /**
     * @return array<Plugin>
     */
    public function getPlugins(): array;
}
