<?php

namespace ApiClientBundle\Client;

use ApiClientBundle\Enum\HttpMethodEnum;
use Http\Client\Common\Plugin;

/**
 * @implements QueryInterface<ServiceInterface, ResponseInterface>
 */
abstract class AbstractQuery implements QueryInterface
{
    protected ?string $path = null;
    protected HttpMethodEnum $method;

    /**
     * @var array<string, mixed>
     */
    protected array $query = [];

    /**
     * @var array<string, mixed>
     */
    protected array $parameters = [];
    protected ?string $body = null;

    /**
     * @var array<string, string|array<string>>
     */
    protected array $headers = [];

    /**
     * @var class-string<ServiceInterface>
     */
    protected string $service;

    /**
     * @var class-string<ResponseInterface>
     */
    protected string $response;
    protected string $format = 'json';

    /**
     * @var array<string, string|array<string>>|null
     */
    protected ?array $files = null;

    /**
     * @var array<Plugin>
     */
    protected array $plugins = [];

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getMethod(): HttpMethodEnum
    {
        return $this->method;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getService(): string
    {
        return $this->service;
    }

    /**
     * @return class-string<ResponseInterface>
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Returns array of file paths or single file path.
     *
     * @return array<string, string|array<string>>|null
     */
    public function getFiles(): null|array
    {
        return $this->files;
    }

    public function getPlugins(): array
    {
        return $this->plugins;
    }
}
