<?php

declare(strict_types=1);

namespace ApiClientBundle\Model;

/**
 * @template TResponse of object
 * @extends AbstractQuery<TResponse, GenericErrorResponse>
 */
abstract class AbstractQueryWithGenericErrorResponse extends AbstractQuery
{
    public function errorResponseClassName(): string
    {
        return GenericErrorResponse::class;
    }
}
