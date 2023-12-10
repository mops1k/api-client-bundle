<?php

namespace ApiClientBundle\Builder;

use ApiClientBundle\Client\QueryInterface;

/**
 * @internal
 */
interface QueryBuilderInterface
{
    public static function build(QueryInterface $query): mixed;
}
