<?php

namespace ApiClientBundle\Interfaces;

interface HeadersInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array;
}
