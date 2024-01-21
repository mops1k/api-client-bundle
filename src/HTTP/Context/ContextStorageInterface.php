<?php

namespace ApiClientBundle\HTTP\Context;

use ApiClientBundle\Client\QueryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ContextStorageInterface
{
    /**
     * @template TQuery of QueryInterface
     *
     * @param TQuery $query
     */
    public function add(QueryInterface $query, RequestInterface $request, ?ResponseInterface $response): static;

    /**
     * @template TQuery of QueryInterface
     *
     * @param TQuery $query
     */
    public static function get(QueryInterface $query): ?ContextInterface;

    /**
     * @template TQuery of QueryInterface
     *
     * @param TQuery $query
     */
    public static function remove(QueryInterface $query): void;

    /**
     * @template TQuery of QueryInterface
     *
     * @param TQuery $query
     */
    public static function has(QueryInterface $query): bool;

    public static function clear(): void;
}
