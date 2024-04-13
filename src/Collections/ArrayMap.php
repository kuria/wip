<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @template TKey of array-key
 * @template TValue of array
 * @extends Map<TKey, TValue>
 * @implements ReadableArrayMap<TKey, TValue>
 */
class ArrayMap extends Map implements ReadableArrayMap
{
    /**
     * @return ArrayList<TValue>
     */
    function values(): ArrayList
    {
        return new ArrayList(\array_values($this->pairs));
    }

    /**
     * @template TMappedKey of array-key
     *
     * @param callable(TKey, TValue):TMappedKey $mapper
     * @return self<TMappedKey, TValue>
     */
    function remap(callable $mapper): self
    {
        return parent::remap($mapper)->asArrays();
    }

    /**
     * @template TColumnKey of array-key
     * @template TIndexBy of array-key
     * @psalm-suppress all
     *
     * @param TColumnKey $key
     * @param TIndexBy|null $indexBy
     *
     * @return ($indexBy is null ? Map<TKey, TValue[TColumnKey]> : Map<TValue[TIndexBy], TValue[TColumnKey]>)
     */
    function column(string|int $key, string|int|null $indexBy = null): Map
    {
        if ($indexBy !== null) {
            return new Map(\array_column($this->pairs, $key, $indexBy));
        }

        // cannot use \array_column() here because it does not preserve keys
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            if (\array_key_exists($key, $v)) {
                $pairs[$k] = $v[$key];
            }
        }

        return new Map($pairs);
    }

    /**
     * @template TIndexBy of array-key
     * @psalm-suppress all
     *
     * @param TIndexBy $key
     * @return self<TValue[TIndexBy], TValue>
     */
    function indexBy(string|int $key): self
    {
        return new self(\array_column($this->pairs, null, $key));
    }
}
