<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @template-covariant T of scalar
 * @extends ReadableList<T>
 */
interface ReadableScalarList extends ReadableList
{
    /**
     * Join all values using a delimiter
     */
    function implode(string $delimiter = ''): string;

    /**
     * Calculate the sum of all values (value1 + ... + valueN)
     *
     * All values must be numeric.
     *
     * Returns 0 if the list is empty.
     */
    function sum(): int|float;

    /**
     * Calculate the product of all values (value1 * ... * valueN)
     *
     * All values must be numeric.
     *
     * Returns 1 if the list is empty.
     */
    function product(): int|float;

    /**
     * Get unique values
     *
     * Returns a new list with unique values.
     *
     * @return static<T>
     */
    function unique(): static;

    /**
     * Compute an intersection with the given iterables
     *
     * Values are converted to strings before the comparison.
     *
     * Returns a new list containing all values from this list that are also present in all the given iterables.
     *
     * @param iterable<scalar> ...$iterables
     * @return static<T>
     */
    function intersect(iterable ...$iterables): static;

    /**
     * Compute a difference between this list and the given iterables
     *
     * Values are converted to strings before the comparison.
     *
     * Returns a new list containing all values from this list that are not present in any of the given iterables.
     *
     * @param iterable<scalar> ...$iterables
     * @return static<T>
     */
    function diff(iterable ...$iterables): static;

    /**
     * Sort the list
     *
     * Returns a new sorted list.
     *
     * @see \SORT_REGULAR compare items normally (don't change types)
     * @see \SORT_NUMERIC compare items numerically
     * @see \SORT_STRING compare items as strings
     * @see \SORT_LOCALE_STRING compare items as strings based on the current locale
     * @see \SORT_NATURAL compare items as strings using "natural ordering" like natsort()
     * @see \SORT_FLAG_CASE can be combined (bitwise OR) with SORT_STRING or SORT_NATURAL to sort strings case-insensitively
     *
     * @return static<T>
     */
    function sort(int $flags = \SORT_REGULAR, bool $reverse = false): static;
}
