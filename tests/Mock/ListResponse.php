<?php

namespace ApiClientBundle\Tests\Mock;

use ApiClientBundle\Attribute\ListResponseField;
use ApiClientBundle\Client\ListResponseInterface;

#[ListResponseField(propertyName: 'data')]
class ListResponse implements ListResponseInterface
{
    /**
     * @param list<string> $data
     */
    public function __construct(public readonly array $data)
    {
    }
}
