<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @template-covariant T of object
 * @extends ReadableList<T>
 */
interface ReadableObjectList extends ReadableList
{
    /**
     * Get unique values
     *
     * Returns a new collection with unique values.
     *
     * @return static<T>
     */
    function unique(): static;

    /**
     * Compute an intersection with the given iterables
     *
     * Returns a new collection containing all objects of this collection that are also present in all the given iterables.
     *
     * @param iterable<object> ...$iterables
     * @return static<T>
     */
    function intersect(iterable ...$iterables): static;

    /**
     * Compute a difference between this collection and the given iterables
     *
     * Returns a new collection containing all objects of this collection that are not present in any of the given iterables.
     *
     * @param iterable<object> ...$iterables
     * @return static<T>
     */
    function diff(iterable ...$iterables): static;

    /**
     * Gather values from object properties
     *
     * @return ReadableList<mixed>
     */
    function column(string $prop): ReadableList;

    /**
     * Build a map using object properties
     *
     * If $valueProp is NULL, the complete objects are mapped to each property value.
     *
     * @return ($valueProp is null ? ReadableObjectMap<array-key, T> : ReadableMap<array-key, mixed>)
     */
    function mapColumn(string $prop, ?string $valueProp = null): ReadableMap;
}
