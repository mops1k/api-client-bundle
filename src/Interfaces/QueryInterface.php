<?php

namespace ApiClientBundle\Interfaces;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @template TResponse of object
 */
interface QueryInterface
{
    public function path(): string;

    public function method(): string;

    public function options(): ParameterBag;

    public function jsonData(): ParameterBag;

    public function queryData(): ParameterBag;

    public function formData(): ParameterBag;

    public function headers(): ParameterBag;

    public function support(ClientConfigurationInterface $clientConfiguration): bool;

    /**
     * @return class-string<TResponse>
     */
    public function responseClassName(): string;

    /**
     * @return SerializerFormatInterface::FORMAT_*
     */
    public function serializerResponseFormat(): string;
}
