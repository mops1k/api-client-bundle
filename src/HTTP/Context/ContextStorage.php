<?php

namespace ApiClientBundle\HTTP\Context;

use ApiClientBundle\Client\QueryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ContextStorage implements ContextStorageInterface
{
    /**
     * @var array<string, ContextInterface>
     */
    private static array $collection = [];

    public function add(QueryInterface $query, RequestInterface $request, ?ResponseInterface $response = null): static
    {
        self::$collection[\spl_object_hash($query)] = new Context(
            request: $request,
            response: $response
        );

        return $this;
    }

    public static function get(QueryInterface $query): ?ContextInterface
    {
        if (false === self::has($query)) {
            return null;
        }

        return self::$collection[\spl_object_hash($query)];
    }

    public static function remove(QueryInterface $query): void
    {
        if (true === self::has($query)) {
            unset(self::$collection[\spl_object_hash($query)]);
        }
    }

    public static function has(QueryInterface $query): bool
    {
        return \array_key_exists(\spl_object_hash($query), self::$collection);
    }

    public static function clear(): void
    {
        self::$collection = [];
    }
}
