<?php

namespace ApiClientBundle\Client;

use ApiClientBundle\Enum\HttpMethodEnum;

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
     * @var array<string, ServiceInterface>
     */
    private array $storedServices = [];

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

    public function getService(): ServiceInterface
    {
        if (array_key_exists($this->service, $this->storedServices)) {
            return $this->storedServices[$this->service];
        }

        $this->storedServices[$this->service] = new $this->service();

        return $this->storedServices[$this->service];
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
}
