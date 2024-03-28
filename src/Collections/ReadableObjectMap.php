<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @template-covariant TKey of array-key
 * @template-covariant TValue of object
 * @extends ReadableMap<TKey, TValue>
 */
interface ReadableObjectMap extends ReadableMap
{
    /**
     * Compute an intersection with the given iterables
     *
     * Returns a new map containing all pairs of this map that are also present in all the given iterables.
     *
     * @param iterable<array-key, object> ...$iterables
     * @return static<TKey, TValue>
     */
    function intersect(iterable ...$iterables): static;

    /**
     * Compute a difference between this map and the given iterables
     *
     * Returns a new map containing all pairs of this map that are not present in any of the given iterables.
     *
     * @param iterable<array-key, object> ...$iterables
     * @return static<TKey, TValue>
     */
    function diff(iterable ...$iterables): static;

    /**
     * Map a property of the contained objects
     *
     * Returns a new map with the gathered values. Preserves original keys if $indexBy is NULL.
     *
     * @return ($indexBy is null ? ReadableMap<TKey, mixed> : ReadableMap<array-key, mixed>)
     */
    function column(string $prop, ?string $indexBy = null): ReadableMap;

    /**
     * Re-index the map using a property of the contained objects
     *
     * Returns a new map indexed by the given property.
     *
     * @return self<array-key, TValue>
     */
    function indexBy(string $prop): self;
}
