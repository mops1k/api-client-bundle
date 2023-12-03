<?php

namespace ApiClientBundle\Tests\Mock;

use ApiClientBundle\Client\AbstractQuery;
use ApiClientBundle\Client\ResponseInterface;
use ApiClientBundle\Enum\HttpMethodEnum;

class QueryWithFile extends AbstractQuery
{
    protected ?string $path = 'upload';
    protected HttpMethodEnum $method = HttpMethodEnum::POST;
    protected ?array $files = ['file' => __DIR__ . '/../Stubs/image.png'];

    /**
     * @var class-string<ResponseInterface>
     */
    protected string $response = ResponseWithFile::class;
    protected string $service = Service::class;
}
