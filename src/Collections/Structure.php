<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @template-covariant TKey of array-key
 * @template-covariant TValue
 * @extends \IteratorAggregate<TKey, TValue>
 */
interface Structure extends \Countable, \IteratorAggregate
{
    /**
     * Convert the structure to an array
     *
     * @return array<TKey, TValue>
     */
    function toArray(): array;

    /**
     * See if the structure is empty
     */
    function isEmpty(): bool;

    /**
     * Get the number of elements in this structure
     *
     * @return non-negative-int
     */
    function count(): int;

    /**
     * Create an iterator
     *
     * @return \Traversable<TKey, TValue>
     */
    function getIterator(): \Traversable;
}
