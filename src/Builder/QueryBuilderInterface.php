<?php

namespace ApiClientBundle\Builder;

use ApiClientBundle\Client\QueryInterface;
use ApiClientBundle\Client\ServiceInterface;

/**
 * @internal
 */
interface QueryBuilderInterface
{
    public static function build(QueryInterface $query, ServiceInterface $service): mixed;
}
