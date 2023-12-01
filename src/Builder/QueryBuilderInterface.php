<?php

namespace ApiClientBundle\Builder;

use ApiClientBundle\Client\QueryInterface;

interface QueryBuilderInterface
{
    /**
     * @return mixed
     */
    public static function build(QueryInterface $query);
}
