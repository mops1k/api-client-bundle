<?php

namespace ApiClientBundle\Tests\Mock;

use ApiClientBundle\Client\AbstractQuery;
use ApiClientBundle\Enum\HttpMethodEnum;

class ListQuery extends AbstractQuery
{
    protected HttpMethodEnum $method = HttpMethodEnum::GET;
    protected ?string $path = 'items';
    protected string $service = Service::class;
    protected string $response = ListResponse::class;
}
