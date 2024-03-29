<?php

namespace ApiClientBundle\Client;

use ApiClientBundle\Enum\HttpMethodEnum;
use Http\Client\Common\Plugin;

/**
 * @template TService of ServiceInterface
 * @template TResponse of ResponseInterface
 */
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

    /**
     * @return class-string<TService>
     */
    public function getService(): string;

    /**
     * @return class-string<TResponse>
     */
    public function getResponse(): string;

    public function getFormat(): string;

    /**
     * @return array<Plugin>
     */
    public function getPlugins(): array;
}
