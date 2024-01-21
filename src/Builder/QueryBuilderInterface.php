<?php

namespace ApiClientBundle\Builder;

use ApiClientBundle\Client\QueryInterface;
use ApiClientBundle\Client\ServiceInterface;

/**
 * @internal
 */
interface QueryBuilderInterface
{
    /**
     * @template TQuery of QueryInterface
     *
     * @param TQuery $query
     */
    public static function build(QueryInterface $query, ServiceInterface $service): mixed;
}
