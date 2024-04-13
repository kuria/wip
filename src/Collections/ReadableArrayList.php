<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @template-covariant T of array
 * @extends ReadableList<T>
 */
interface ReadableArrayList extends ReadableList
{
    /**
     * Gather values from array keys
     *
     * @return ReadableList<mixed>
     */
    function column(string|int $key): ReadableList;

    /**
     * Build a map using array keys
     *
     * If $valueKey is NULL, the complete arrays are mapped to each key value.
     *
     * @return ($valueKey is null ? ReadableArrayMap<array-key, T> : ReadableMap<array-key, mixed>)
     */
    function mapColumn(string|int $key, string|int|null $valueKey = null): ReadableMap;
}
