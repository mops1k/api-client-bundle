<?php

namespace ApiClientBundle\HTTP;

use ApiClientBundle\Client\QueryInterface;
use ApiClientBundle\Client\ResponseInterface;

interface HttpClientInterface
{
    public function request(QueryInterface $query): ResponseInterface;
}
