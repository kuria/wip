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
     * Returns a new list with unique values.
     *
     * @return static<T>
     */
    function unique(): static;

    /**
     * Compute an intersection with the given iterables
     *
     * Returns a new list containing all objects from this list that are also present in all the given iterables.
     *
     * @param iterable<object> ...$iterables
     * @return static<T>
     */
    function intersect(iterable ...$iterables): static;

    /**
     * Compute a difference between this list and the given iterables
     *
     * Returns a new list containing all objects from this list that are not present in any of the given iterables.
     *
     * @param iterable<object> ...$iterables
     * @return static<T>
     */
    function diff(iterable ...$iterables): static;

    /**
     * Gather values of the given property
     *
     * @template TProp of string
     *
     * @param TProp $prop
     * @return ReadableList<T[TProp]>
     */
    function column(string $prop): ReadableList;

    /**
     * Build a map using the given property
     *
     * - $prop must point to values that are valid array keys
     * - if $valueProp is NULL, the complete objects are mapped to each property value
     *
     * @template TProp of string
     * @template TValueProp of string
     *
     * @param TProp $prop
     * @param TValueProp|null $valueProp
     *
     * @return ($valueProp is null ? ReadableObjectMap<T[TProp], T> : ReadableMap<T[TProp], T[TValueProp]>)
     */
    function mapColumn(string $prop, ?string $valueProp = null): ReadableMap;
}
