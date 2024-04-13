<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * Sequential list of arrays
 *
 * @template T of array
 * @extends Collection<T>
 * @implements ReadableArrayList<T>
 */
class ArrayList extends Collection implements ReadableArrayList
{
    /**
     * @template TMappedKey of array-key
     *
     * @param callable(non-negative-int, T):TMappedKey $mapper
     * @return ArrayMap<TMappedKey, T>
     */
    function map(callable $mapper): ArrayMap
    {
        return parent::map($mapper)->asArrays();
    }

    /**
     * @return ArrayMap<non-negative-int, T>
     */
    function toMap(): ArrayMap
    {
        return new ArrayMap($this->values);
    }

    /**
     * @template TKey of array-key
     * @psalm-suppress all
     *
     * @param TKey $key
     * @return Collection<T[TKey]>
     */
    function column(string|int $key): Collection
    {
        return new Collection(\array_column($this->values, $key));
    }

    /**
     * @template TKey of array-key
     * @template TValueKey of array-key
     * @psalm-suppress all
     *
     * @param TKey $key
     * @param TValueKey|null $valueKey
     *
     * @return ($valueKey is null ? ArrayMap<T[TKey], T> : Map<T[TKey], T[TValueKey]>)
     */
    function mapColumn(string|int $key, string|int|null $valueKey = null): Map
    {
        if ($valueKey !== null) {
            return new Map(\array_column($this->values, $valueKey, $key));
        }

        return new ArrayMap(\array_column($this->values, null, $key));
    }

}
