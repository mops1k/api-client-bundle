<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Interfaces\SerializerFormatInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template TResponse of object
 * @template TErrorResponse of GenericErrorResponse
 *
 * @implements QueryInterface<TResponse, TErrorResponse>
 */
abstract class AbstractQuery implements QueryInterface
{
    /**
     * @var ParameterBag<mixed>
     */
    private ParameterBag $options;

    /**
     * @var ParameterBag<mixed>
     */
    private ParameterBag $jsonData;

    /**
     * @var ParameterBag<mixed>
     */
    private ParameterBag $queryData;

    /**
     * @var ParameterBag<mixed>
     */
    private ParameterBag $formData;

    /**
     * @var ParameterBag<mixed>
     */
    private ParameterBag $files;

    /**
     * @var ParameterBag<mixed>
     */
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

    /**
     * @return ParameterBag<mixed>
     */
    public function options(): ParameterBag
    {
        return $this->options;
    }

    /**
     * @return ParameterBag<mixed>
     */
    public function jsonData(): ParameterBag
    {
        return $this->jsonData;
    }

    /**
     * @return ParameterBag<mixed>
     */
    public function queryData(): ParameterBag
    {
        return $this->queryData;
    }

    /**
     * @return ParameterBag<mixed>
     */
    public function formData(): ParameterBag
    {
        return $this->formData;
    }

    /**
     * @return ParameterBag<mixed>
     */
    public function files(): ParameterBag
    {
        return $this->files;
    }

    /**
     * @return ParameterBag<mixed>
     */
    public function headers(): ParameterBag
    {
        return $this->headers;
    }

    public function serializerResponseFormat(): string
    {
        return SerializerFormatInterface::FORMAT_JSON;
    }
}
