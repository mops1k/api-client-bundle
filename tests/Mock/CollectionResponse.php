<?php

namespace ApiClientBundle\Tests\Mock;

use ApiClientBundle\Attribute\CollectionResponseField;
use ApiClientBundle\Client\CollectionResponseInterface;

#[CollectionResponseField(propertyName: 'data')]
class CollectionResponse implements CollectionResponseInterface
{
    /**
     * @param list<string> $data
     */
    public function __construct(public readonly array $data)
    {
    }
}
