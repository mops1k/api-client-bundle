<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Interfaces\SerializerFormatInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template TResponse of object
 * @template TErrorResponse of GenericErrorResponse
 * @implements QueryInterface<TResponse, TErrorResponse>
 */
abstract class AbstractQuery implements QueryInterface
{
    private ParameterBag $options;
    private ParameterBag $jsonData;
    private ParameterBag $queryData;
    private ParameterBag $formData;
    private ParameterBag $files;
    private ParameterBag $headers;

    public function __construct()
    {
        $this->options = new ParameterBag();
        $this->jsonData = new ParameterBag();
        $this->queryData = new ParameterBag();
        $this->formData = new ParameterBag();
        $this->files = new ParameterBag();
        $this->headers = new ParameterBag();
    }

    public function method(): string
    {
        return Request::METHOD_GET;
    }

    public function options(): ParameterBag
    {
        return $this->options;
    }

    public function jsonData(): ParameterBag
    {
        return $this->jsonData;
    }

    public function queryData(): ParameterBag
    {
        return $this->queryData;
    }

    public function formData(): ParameterBag
    {
        return $this->formData;
    }

    public function files(): ParameterBag
    {
        return $this->files;
    }

    public function headers(): ParameterBag
    {
        return $this->headers;
    }

    public function serializerResponseFormat(): string
    {
        return SerializerFormatInterface::FORMAT_JSON;
    }
}
