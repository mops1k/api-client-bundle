<?php

namespace ApiClientBundle\Builder;

use ApiClientBundle\Client\QueryInterface;
use ApiClientBundle\Client\ServiceInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\UriInterface;

class RequestUriBuilder implements QueryBuilderInterface
{
    public static function build(QueryInterface $query, ServiceInterface $service): UriInterface
    {
        return Psr17FactoryDiscovery::findUriFactory()
                                    ->createUri()
                                    ->withHost($service->getHost())
                                    ->withPort($service->getPort())
                                    ->withScheme($service->getScheme())
                                    ->withPath($query->getPath() ?? '/')
                                    ->withQuery(http_build_query($query->getQuery()))
        ;
    }
}
