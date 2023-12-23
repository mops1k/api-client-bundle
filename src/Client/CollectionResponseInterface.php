<?php

namespace ApiClientBundle\Client;

use ApiClientBundle\Attribute\CollectionResponseField;

#[CollectionResponseField('items')]
interface CollectionResponseInterface extends ResponseInterface
{
}
