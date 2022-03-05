<?php

namespace ApiClientBundle\Interfaces;

/**
 * @extends \Iterator<int, mixed>
 */
interface ImmutableCollectionInterface extends \Iterator, \Countable
{
    /**
     * Checks whether an element is contained in the collection.
     * This is an O(n) operation, where n is the size of the collection.
     */
    public function contains(mixed $element): bool;

    /**
     * Checks whether the collection contains an element with the specified key/index.
     */
    public function containsKey(int $key): bool;

    /**
     * Checks whether the collection is empty (contains no elements).
     */
    public function isEmpty(): bool;

    /**
     * Gets the element at the specified key/index.
     */
    public function get(int $key): mixed;

    /**
     * Gets all keys/indices of the collection.
     *
     * @return array<int>
     */
    public function getKeys(): array;

    /**
     * Gets all values of the collection.
     *
     * @return array<mixed>
     */
    public function getValues(): array;

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array<mixed>
     */
    public function toArray(): array;


    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     */
    public function first(): mixed;

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     */
    public function last(): mixed;

    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     *
     * @return iterable<mixed>
     */
    public function filter(\Closure $p): iterable;
}
