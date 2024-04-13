<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @template-covariant TKey of array-key
 * @template-covariant TValue of array
 * @extends ReadableMap<TKey, TValue>
 */
interface ReadableArrayMap extends ReadableMap
{
    /**
     * Map a key of the contained arrays
     *
     * Returns a new map with the gathered values. Preserves original keys if $indexBy is NULL.
     *
     * @template TColumnKey of array-key
     * @template TIndexBy of array-key
     *
     * @param TColumnKey $key
     * @param TIndexBy|null $indexBy
     *
     * @return ($indexBy is null ? ReadableMap<TKey, TValue[TColumnKey]> : ReadableMap<TValue[TIndexBy], TValue[TColumnKey]>)
     */
    function column(string|int $key, string|int|null $indexBy = null): ReadableMap;

    /**
     * Re-index the map using a key of the contained arrays
     *
     * Returns a new map indexed by the given key. Values for that key must be valid array keys.
     *
     * @template TIndexBy of array-key
     *
     * @param TIndexBy $key
     * @return self<TValue[TIndexBy], TValue>
     */
    function indexBy(string|int $key): self;
}
