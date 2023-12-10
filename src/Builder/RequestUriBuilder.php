<?php

namespace ApiClientBundle\Builder;

use ApiClientBundle\Client\QueryInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\UriInterface;

class RequestUriBuilder implements QueryBuilderInterface
{
    public static function build(QueryInterface $query): UriInterface
    {
        return Psr17FactoryDiscovery::findUriFactory()
                                    ->createUri()
                                    ->withHost($query->getService()->getHost())
                                    ->withPort($query->getService()->getPort())
                                    ->withScheme($query->getService()->getScheme())
                                    ->withPath($query->getPath() ?? '/')
                                    ->withQuery(http_build_query($query->getQuery()))
        ;
    }
}
