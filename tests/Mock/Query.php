<?php

namespace ApiClientBundle\Tests\Mock;

use ApiClientBundle\Client\AbstractQuery;
use ApiClientBundle\Enum\HttpMethodEnum;

class Query extends AbstractQuery
{

    protected ?string $path = 'posts';
    protected HttpMethodEnum $method = HttpMethodEnum::GET;

    protected string $service = Service::class;
    protected string $response = Response::class;
}
