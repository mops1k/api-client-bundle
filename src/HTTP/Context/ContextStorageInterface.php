<?php

namespace ApiClientBundle\HTTP\Context;

use ApiClientBundle\Client\QueryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ContextStorageInterface
{
    public function add(QueryInterface $query, RequestInterface $request, ?ResponseInterface $response): static;
    public static function get(QueryInterface $query): ?ContextInterface;
    public static function remove(QueryInterface $query): void;
    public static function has(QueryInterface $query): bool;
    public static function clear(): void;
}
