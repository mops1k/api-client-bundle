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

    public function options(): ParameterBag;

    public function headers(): ParameterBag;

    public function isAsync(): bool;
}
