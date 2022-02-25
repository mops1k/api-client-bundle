<?php

namespace ApiClientBundle\Exceptions;

use ApiClientBundle\Interfaces\GenericErrorResponseInterface;
use ProxyManager\Proxy\GhostObjectInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ErrorResponseException extends \RuntimeException implements GenericErrorResponseInterface
{
    /**
     * @param GhostObjectInterface<object> $responseObject
     */
    public function __construct(
        private ResponseInterface $response,
        GhostObjectInterface $responseObject,
        \Throwable $previous = null,
    ) {
        $responseObject->setProxyInitializer(fn (
            GhostObjectInterface $ghostObject,
            string $method,
            array $parameters,
            &$initializer,
            array $properties,
        ) => $initializer = null);

        parent::__construct(Response::$statusTexts[$this->response->getStatusCode()], $this->response->getStatusCode(), $previous);
    }

    public function getRawContent(): string
    {
        return $this->response->getContent(false);
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders(false);
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }
}
