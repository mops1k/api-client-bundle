<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractClientConfiguration implements ClientConfigurationInterface
{
    private ParameterBag $options;
    private ParameterBag $headers;

    public function __construct()
    {
        $this->options = new ParameterBag();
        $this->headers = new ParameterBag();
    }

    public function options(): ParameterBag
    {
        return $this->options;
    }

    public function headers(): ParameterBag
    {
        return $this->headers;
    }

    public function isAsync(): bool
    {
        return false;
    }
}
