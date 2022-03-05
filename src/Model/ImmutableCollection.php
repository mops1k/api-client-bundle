<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\ImmutableCollectionInterface;

class ImmutableCollection implements ImmutableCollectionInterface
{
    private int $position = 0;

    /**
     * @param array<int, mixed> $elements
     */
    public function __construct(protected array $elements = [])
    {
    }

    public function count(): int
    {
        return \count($this->elements);
    }

    public function current(): mixed
    {
        return $this->elements[$this->position] ?? null;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->elements[$this->position]);
    }

    public function rewind(): void
    {
        if ($this->position > 0) {
            --$this->position;

            return;
        }

        $this->position = 0;
    }

    public function contains(mixed $element): bool
    {
        return \in_array($element, $this->elements, true);
    }

    public function containsKey(int $key): bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    public function get(int $key): mixed
    {
        return $this->elements[$key] ?? null;
    }

    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    public function getValues(): array
    {
        return array_values($this->elements);
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function first(): mixed
    {
        $this->position = 0;

        return $this->elements[$this->position] ?? null;
    }

    public function last(): mixed
    {
        $this->position = $this->count() - 1;

        return $this->elements[$this->position] ?? null;
    }

    public function filter(\Closure $p): self
    {
        return new self(array_filter($this->elements, $p, ARRAY_FILTER_USE_BOTH));
    }
}
