<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\QueryInterface;
use ApiClientBundle\Interfaces\SerializerFormatInterface;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractQuery implements QueryInterface
{
    private ParameterBag $options;
    private ParameterBag $jsonData;
    private ParameterBag $queryData;
    private ParameterBag $formData;
    private ParameterBag $headers;

    #[Pure]
    public function __construct()
    {
        $this->options = new ParameterBag();
        $this->jsonData = new ParameterBag();
        $this->queryData = new ParameterBag();
        $this->formData = new ParameterBag();
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

    public function headers(): ParameterBag
    {
        return $this->headers;
    }

    public function serializerResponseFormat(): string
    {
        return SerializerFormatInterface::FORMAT_JSON;
    }
}
