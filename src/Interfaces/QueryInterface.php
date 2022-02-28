<?php

namespace ApiClientBundle\Interfaces;

use ApiClientBundle\Model\GenericErrorResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @template TResponse of object
 * @template TErrorResponse of GenericErrorResponse
 */
interface QueryInterface
{
    public function path(): string;

    public function method(): string;

    public function options(): ParameterBag;

    public function jsonData(): ParameterBag;

    public function queryData(): ParameterBag;

    public function formData(): ParameterBag;

    public function files(): ParameterBag;

    public function headers(): ParameterBag;

    public function support(ClientConfigurationInterface $clientConfiguration): bool;

    /**
     * @return class-string<TResponse>
     */
    public function responseClassName(): string;

    /**
     * @return class-string<TErrorResponse>
     */
    public function errorResponseClassName(): string;

    /**
     * @return SerializerFormatInterface::FORMAT_*
     */
    public function serializerResponseFormat(): string;
}
