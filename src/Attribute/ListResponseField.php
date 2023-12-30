<?php

namespace ApiClientBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ListResponseField
{
    public function __construct(public string $propertyName)
    {
    }
}
