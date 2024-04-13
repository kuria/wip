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
     * @return ($indexBy is null ? ReadableMap<TKey, mixed> : ReadableMap<array-key, mixed>)
     */
    function column(string|int $key, string|int|null $indexBy = null): ReadableMap;

    /**
     * Re-index the map using a key of the contained arrays
     *
     * Returns a new map indexed by the given key.
     *
     * @return self<array-key, TValue>
     */
    function indexBy(string|int $key): self;
}
