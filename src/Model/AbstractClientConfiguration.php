<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractClientConfiguration implements ClientConfigurationInterface
{
    /**
     * @var ParameterBag<mixed>
     */
    private ParameterBag $options;

    /**
     * @var ParameterBag<mixed>
     */
    private ParameterBag $headers;

    public function __construct()
    {
        $this->options = new ParameterBag();
        $this->headers = new ParameterBag();
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
    public function headers(): ParameterBag
    {
        return $this->headers;
    }

    public function isAsync(): bool
    {
        return false;
    }
}
