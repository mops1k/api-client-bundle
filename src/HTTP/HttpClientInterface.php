<?php

namespace ApiClientBundle\HTTP;

use ApiClientBundle\Client\QueryInterface;
use ApiClientBundle\Client\ResponseInterface;
use ApiClientBundle\Client\ServiceInterface;

interface HttpClientInterface
{
    /**
     * @param QueryInterface<ServiceInterface, ResponseInterface> $query
     */
    public function request(QueryInterface $query): ResponseInterface;
}
