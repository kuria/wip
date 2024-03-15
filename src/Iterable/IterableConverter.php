<?php declare(strict_types=1);

namespace Kuria\Iterable;

abstract class IterableConverter
{
    /**
     * Convert an iterable value to an array
     *
     * - if the value is already an array, it is returned unchanged
     * - if an iterator yields multiple values with the same key, only the last value will be present in the array
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey, TValue> $iterable
     * @return array<TKey, TValue>
     */
    static function toArray(iterable $iterable): array
    {
        return $iterable instanceof \Traversable ? \iterator_to_array($iterable) : $iterable;
    }

    /**
     * Convert a list of iterables to a list of arrays
     *
     * The operation on individual iterables obeys the same rules as {@see toArray}.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<iterable<TKey, TValue>> $iterables
     * @return list<array<TKey, TValue>>
     */
    static function toArrays(iterable $iterables): array
    {
        $arrays = [];

        foreach ($iterables as $iterable) {
            $arrays[] = $iterable instanceof \Traversable ? \iterator_to_array($iterable) : $iterable;
        }

        return $arrays;
    }

    /**
     * Convert an iterable value to an array with consecutive integer indexes
     *
     * - if the value is already an array, only its values will be returned (keys are discarded)
     * - if the value is traversable, all its values will be returned
     *
     * @template T
     *
     * @param iterable<T> $iterable
     * @return list<T>
     */
    static function toList(iterable $iterable): array
    {
        if ($iterable instanceof \Traversable) {
            return \iterator_to_array($iterable, false);
        }

        if (\array_is_list($iterable)) {
            return $iterable;
        }

        return \array_values($iterable);
    }

    /**
     * Convert a list of iterables to a list of lists
     *
     * The operation on individual iterables obeys the same rules as {@see toList}.
     *
     * @template T
     *
     * @param iterable<iterable<T>> $iterables
     * @return list<list<T>>
     */
    static function toLists(iterable $iterables): array
    {
        $lists = [];

        foreach ($iterables as $iterable) {
            $lists[] = self::toList($iterable);
        }

        return $lists;
    }
}
