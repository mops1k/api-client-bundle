<?php

namespace ApiClientBundle\Builder;

use ApiClientBundle\Client\QueryInterface;

class RequestBodyBuilder implements QueryBuilderInterface
{
    public static function build(QueryInterface $query): ?string
    {
        $body = $query->getBody();
        if (count($query->getParameters()) === 0) {
            return $body;
        }

        $denormalizedParameters = [];
        foreach ($query->getParameters() as $key => $value) {
            $denormalizedParameters[] = $key . '=' . $value;
        }
        if (null === $body && count($denormalizedParameters) > 0) {
            $body = \implode('&', $denormalizedParameters);
        }

        return $body;
    }
}
