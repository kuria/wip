<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @template-covariant TKey of array-key
 * @template-covariant TValue of scalar
 * @extends ReadableMap<TKey, TValue>
 */
interface ReadableScalarMap extends ReadableMap
{
    /**
     * Swap keys and values
     *
     * @return self<array-key, TKey>
     */
    function flip(): self;

    /**
     * Compute an intersection with the given iterables
     *
     * Values are converted to a string before the comparison.
     *
     * Returns a new map containing all pairs of this map that are also present in all the given iterables.
     *
     * @param iterable<array-key, scalar> ...$iterables
     * @return static<TKey, TValue>
     */
    function intersect(iterable ...$iterables): static;

    /**
     * Compute a difference between this map and the given iterables
     *
     * Values are converted to a string before the comparison.
     *
     * Returns a new map containing all pairs of this map that are not present in any of the given iterables.
     *
     * @param iterable<array-key, scalar> ...$iterables
     * @return static<TKey, TValue>
     */
    function diff(iterable ...$iterables): static;

    /**
     * Sort the map using its values
     *
     * Returns a new sorted map.
     *
     * @see \SORT_REGULAR compare items normally (don't change types)
     * @see \SORT_NUMERIC compare items numerically
     * @see \SORT_STRING compare items as strings
     * @see \SORT_LOCALE_STRING compare items as strings based on the current locale
     * @see \SORT_NATURAL compare items as strings using "natural ordering" like natsort()
     * @see \SORT_FLAG_CASE can be combined (bitwise OR) with SORT_STRING or SORT_NATURAL to sort strings case-insensitively
     *
     * @return static<TKey, TValue>
     */
    function sort(int $flags = SORT_REGULAR, bool $reverse = false): static;
}
