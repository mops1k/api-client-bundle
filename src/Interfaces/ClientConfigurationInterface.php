<?php

namespace ApiClientBundle\Interfaces;

use Symfony\Component\HttpFoundation\ParameterBag;

interface ClientConfigurationInterface
{
    public const SCHEME_HTTP = 'http';
    public const SCHEME_SSL = 'https';

    public function domain(): string;

    /**
     * @return self::SCHEME_*
     */
    public function scheme(): string;

    /**
     * @return ParameterBag<mixed>
     */
    public function options(): ParameterBag;

    /**
     * @return ParameterBag<mixed>
     */
    public function headers(): ParameterBag;

    public function isAsync(): bool;
}
