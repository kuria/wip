<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @template-covariant T of array
 * @extends ReadableList<T>
 */
interface ReadableArrayList extends ReadableList
{
    /**
     * Gather values using the given key
     *
     * @template TKey of array-key
     *
     * @param TKey $key
     * @return ReadableList<T[TKey]>
     */
    function column(string|int $key): ReadableList;

    /**
     * Build a map using the given key
     *
     * - $key must point to values that are valid array keys
     * - if $valueKey is NULL, the complete arrays are mapped to each key value
     *
     * @template TKey of array-key
     * @template TValueKey of array-key
     *
     * @param TKey $key
     * @param TValueKey|null $valueKey
     *
     * @return ($valueKey is null ? ReadableArrayMap<T[TKey], T> : ReadableMap<T[TKey], T[TValueKey]>)
     */
    function mapColumn(string|int $key, string|int|null $valueKey = null): ReadableMap;
}
