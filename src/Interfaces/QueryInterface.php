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

    /**
     * @return ParameterBag<mixed>
     */
    public function options(): ParameterBag;

    /**
     * @return ParameterBag<mixed>
     */
    public function jsonData(): ParameterBag;

    /**
     * @return ParameterBag<mixed>
     */
    public function queryData(): ParameterBag;

    /**
     * @return ParameterBag<mixed>
     */
    public function formData(): ParameterBag;

    /**
     * @return ParameterBag<mixed>
     */
    public function files(): ParameterBag;

    /**
     * @return ParameterBag<mixed>
     */
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
