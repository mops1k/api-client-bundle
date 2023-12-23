<?php

namespace ApiClientBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class CollectionResponseField
{
    public function __construct(public string $propertyName)
    {
    }
}
