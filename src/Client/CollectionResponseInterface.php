<?php

namespace ApiClientBundle\Client;

use ApiClientBundle\Attribute\ListResponseField;

/**
 * @deprecated Will be removed in version 1.1. Use instead: ApiClientBundle\Client\ListResponseInterface
 */
#[ListResponseField('items')]
interface CollectionResponseInterface extends ListResponseInterface
{
}
