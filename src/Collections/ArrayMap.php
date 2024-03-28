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
     * @return ($indexBy is null ? Map<TKey, mixed> : Map<array-key, mixed>)
     */
    function column(string $key, int|string|null $indexBy = null): Map
    {
        if ($indexBy !== null) {
            return new Map(\array_column($this->pairs, $key, $indexBy));
        }

        // cannot use \array_column() here because it does not preserve keys
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            if (\array_key_exists($key, $v)) {
                /** @psalm-suppress MixedAssignment */
                $pairs[$k] = $v[$key];
            }
        }

        return new Map($pairs);
    }

    /**
     * @return self<array-key, TValue>
     */
    function indexBy(string $key): self
    {
        return new self(\array_column($this->pairs, null, $key));
    }
}
