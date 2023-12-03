<?php

namespace ApiClientBundle\Tests\Mock;

use ApiClientBundle\Client\AbstractService;

class Service extends AbstractService
{
    protected string $scheme = 'https';
    protected string $host = 'v2.convertapi.com';
}
