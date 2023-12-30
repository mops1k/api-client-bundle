<?php

namespace ApiClientBundle\Client;

use ApiClientBundle\Attribute\ListResponseField;

#[ListResponseField('items')]
interface ListResponseInterface extends ResponseInterface
{
}
