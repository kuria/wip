<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\Iterable\IterableConverter;
use Kuria\Maybe\{Maybe, Some, None};

/**
 * Sequential list of values
 *
 * @template T
 * @implements \ArrayAccess<int, T>
 * @implements \IteratorAggregate<non-negative-int, T>
 * @phpstan-consistent-constructor
 */
class Collection implements \Countable, \ArrayAccess, \IteratorAggregate
{
    /** @var list<T> */
    private array $values;

    /**
     * @param list<T> $values
     */
    function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * Create a collection from an iterable
     *
     * @template TValue
     *
     * @param iterable<TValue> $values
     * @return static<TValue>
     */
    static function create(iterable $values = []): self
    {
        return new static(IterableConverter::toList($values));
    }

    /**
     * Create a collection from the passed arguments
     *
     * @template TValue
     *
     * @param TValue ...$values
     * @return static<TValue>
     */
    static function collect(mixed ...$values): self
    {
        return new static($values);
    }

    /**
     * Create a collection and populate it with repeated values
     *
     * @template TValue
     *
     * @param TValue $value
     * @return static<TValue>
     */
    static function fill(mixed $value, int $count): self
    {
        if ($count <= 0) {
            return new static();
        }

        return new static(\array_fill(0, $count, $value));
    }

    /**
     * Create a collection by splitting a string
     *
     * If $limit is negative, all parts except the last -$limit will be returned.
     *
     * @param non-empty-string $delimiter
     * @return static<string>
     */
    static function explode(string $string, string $delimiter, int $limit = PHP_INT_MAX): self
    {
        return new static(\explode($delimiter, $string, $limit));
    }

    /**
     * Replace all values with the given iterable
     *
     * @param iterable<T> $values
     */
    function setValues(iterable $values): void
    {
        $this->values = IterableConverter::toList($values);
    }

    /**
     * Get all values as an array
     *
     * @return list<T>
     */
    function toArray(): array
    {
        return $this->values;
    }

    /**
     * See if the collection is empty
     */
    function isEmpty(): bool
    {
        return \count($this->values) === 0;
    }

    /**
     * See if the given index exists
     */
    function has(int $index): bool
    {
        return \array_key_exists($index, $this->values);
    }

    /**
     * See if the given value exists
     *
     * @param T $value
     */
    function contains(mixed $value): bool
    {
        return \in_array($value, $this->values, true);
    }

    /**
     * Try to find the first occurrence of a value
     *
     * @param T $value
     * @return Maybe<non-negative-int> the found index
     */
    function find(mixed $value): Maybe
    {
        $index = \array_search($value, $this->values, true);

        /** @var Maybe<non-negative-int> */
        return $index !== false ? new Some($index) : new None();
    }

    /**
     * Try to find the first occurrence of a value accepted by the filter
     *
     * @param callable(T):bool $filter
     * @return Maybe<non-negative-int>
     */
    function findUsing(callable $filter): Maybe
    {
        foreach ($this->values as $index => $value) {
            if ($filter($value)) {
                /** @var Maybe<non-negative-int> */
                return new Some($index);
            }
        }

        return new None();
    }

    /**
     * Get value at the given index
     *
     * @param int $index
     * @return Maybe<T>
     */
    function get(int $index): Maybe
    {
        return \array_key_exists($index, $this->values) ? new Some($this->values[$index]) : new None();
    }

    /**
     * Set a value at the given index
     *
     * The index must point to an existing value or the end of the collection.
     *
     * @throws \OutOfRangeException if $index is not valid
     *
     * @param T $value
     */
    function set(int $index, mixed $value): void
    {
        $count = \count($this->values);

        if ($index < 0) {
            throw new \OutOfRangeException('Negative index given');
        }

        if ($index > $count) {
            throw new \OutOfRangeException(\sprintf(
                'Cannot set value at index %d because it is beyond the end of the collection %d',
                $index,
                $count,
            ));
        }

        $this->values[$index] = $value;
    }

    /**
     * Get the first value
     *
     * @return Maybe<T>
     */
    function first(): Maybe
    {
        return \count($this->values) > 0 ? new Some($this->values[0]) : new None();
    }

    /**
     * Get the last value
     *
     * @return Maybe<T>
     */
    function last(): Maybe
    {
        $count = \count($this->values);

        return $count > 0 ? new Some($this->values[$count - 1]) : new None();
    }

    /**
     * Get all indexes
     *
     * @return list<non-negative-int>
     */
    function indexes(): array
    {
        /** @var list<non-negative-int> */
        return \array_keys($this->values);
    }

    /**
     * Extract a slice of the collection
     *
     * Both $index and $length can be negative, in which case they are relative to the end of the collection.
     *
     * @return static<T>
     */
    function slice(int $index, ?int $length = null): self
    {
        return new static(\array_slice($this->values, $index, $length));
    }

    /**
     * Push one or more values onto the end of the collection
     *
     * @param T ...$values
     */
    function push(mixed ...$values): void
    {
        \array_push($this->values, ...$values);
    }

    /**
     * Pop a value off the end of the collection
     *
     * @return Maybe<T>
     */
    function pop(): Maybe
    {
        return \count($this->values) > 0 ? new Some(\array_pop($this->values)) : new None();
    }

    /**
     * Prepend one or more values to the beginning of the collection
     *
     * Multiple values are prepended as a whole, so they stay in the same order.
     *
     * @param T ...$values
     */
    function unshift(mixed ...$values): void
    {
        \array_unshift($this->values, ...$values);
    }

    /**
     * Shift a value off the beginning of the collection
     *
     * @return Maybe<T>
     */
    function shift(): Maybe
    {
        return \count($this->values) > 0 ? new Some(\array_shift($this->values)) : new None();
    }

    /**
     * Insert one or more values at the given index
     *
     * Any existing values at or after the index will be re-indexed.
     *
     * If $index is negative, it is treated as an offset from the end of the collection.
     *
     * @param T ...$values
     */
    function insert(int $index, mixed ...$values): void
    {
        if (\count($values) > 0) {
            \array_splice($this->values, $index, 0, $values);
        }
    }

    /**
     * Pad the collection with a value to the specified length
     *
     * If $length is positive, the new values are appended. Otherwise, they are prepended.
     *
     * @param T $value
     */
    function pad(int $length, mixed $value): void
    {
        $this->values = \array_pad($this->values, $length, $value);
    }

    /**
     * Remove values at the given indexes
     *
     * Any values after each removed index will be re-indexed.
     */
    function remove(int ...$indexes): void
    {
        $numIndexes = \count($indexes);

        if ($numIndexes === 0 || \count($this->values) === 0) {
            return;
        }

        if ($numIndexes === 1) {
            \array_splice($this->values, $indexes[0], 1);

            return;
        }

        foreach ($indexes as $index) {
            unset($this->values[$index]);
        }

        // reindex values
        $this->values = \array_values($this->values);
    }

    /**
     * Remove all values
     */
    function clear(): void
    {
        $this->values = [];
    }

    /**
     * Remove or replace a part of the collection
     *
     * Both $index and $length can be negative, in which case they are relative to the end of the collection.
     *
     * If $length is NULL, all elements until the end of the collection are removed or replaced.
     *
     * @param iterable<T> $replacement
     */
    function splice(int $index, ?int $length = null, ?iterable $replacement = null): void
    {
        \array_splice(
            $this->values,
            $index,
            $length ?? count($this->values),
            $replacement !== null ? IterableConverter::toList($replacement) : null,
        );
    }

    /**
     * Calculate the sum of all values (value1 + ... + valueN)
     *
     * Note that this method only operates on numeric values.
     *
     * Returns 0 if the collection is empty.
     */
    function sum(): int|float
    {
        return \array_sum($this->values);
    }

    /**
     * Calculate the product of all values (value1 * ... * valueN)
     *
     * Note that this method only operates on numeric values.
     *
     * Returns 1 if the collection is empty.
     */
    function product(): int|float
    {
        return \array_product($this->values);
    }

    /**
     * Join all values using a delimiter
     *
     * All values must be convertable to a string.
     */
    function implode(string $delimiter = ''): string
    {
        return \implode($delimiter, $this->values);
    }

    /**
     * Reduce the collection to a single value
     *
     * The callback should accept 2 arguments (iteration result and current value)
     * and return a new iteration result. The returned iteration result will be
     * used in subsequent callback invocations.
     *
     * Returns the final iteration result or $initial if the collection is empty.
     *
     * @template TResult
     * @template TInitial
     *
     * @param callable(TResult|TInitial, T):TResult $reducer
     * @param TInitial $initial
     * @return TResult|TInitial
     */
    function reduce(callable $reducer, mixed $initial = null): mixed
    {
        return \array_reduce($this->values, $reducer, $initial);
    }

    /**
     * Reverse the collection
     *
     * Returns a new collection with values in reverse order.
     *
     * @return static<T>
     */
    function reverse(): self
    {
        return new static(\array_reverse($this->values));
    }

    /**
     * Split the collection into chunks of the given size
     *
     * The last chunk might be smaller if collection size is not a multiple of $size.
     *
     * @param positive-int $size
     * @return list<static>
     */
    function chunk(int $size): array
    {
        $chunks = [];

        foreach (\array_chunk($this->values, $size) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return $chunks;
    }

    /**
     * Split the collection into the given number of chunks
     *
     * The last chunk might be smaller if collection size is not a multiple of $size.
     *
     * @return list<static>
     */
    function split(int $number): array
    {
        $count = \count($this->values);

        if ($count === 0 || $number < 1) {
            return [];
        }

        // @phpstan-ignore argument.type (cannot be less than 1)
        return $this->chunk((int) \ceil($count / $number));
    }

    /**
     * Get unique values
     *
     * Values are compared in non-strict mode.
     *
     * Returns a new collection with unique values.
     *
     * @return static<T>
     */
    function unique(): self
    {
        return new static(\array_values(\array_unique($this->values, \SORT_REGULAR)));
    }

    /**
     * Get values in random order
     *
     * Returns a new collection with values in random order.
     *
     * @return static<T>
     */
    function shuffle(): self
    {
        $values = $this->values;
        \shuffle($values);

        return new static($values);
    }

    /**
     * Get N random values from the collection
     *
     * - if $count is greater than the size of the collection, all values will be returned
     * - if $count is less than 1, an empty collection will be returned
     *
     * Returns a new collection with the randomly chosen values.
     *
     * @return static<T>
     */
    function random(int $count): self
    {
        if ($count <= 0) {
            return new static();
        }

        if ($count >= $this->count()) {
            return $this->shuffle();
        }

        $keys = \array_rand($this->values, $count);
        $values = [];

        foreach ((array) $keys as $k) {
            $values[] = $this->values[$k];
        }

        return new static($values);
    }

    /**
     * Gather values from a property or array index of all object or array values
     *
     * Returns a new collection with the gathered values.
     *
     * @return static<mixed>
     */
    function column(int|string $key): self
    {
        return new static(\array_column($this->values, $key));
    }

    /**
     * Build a map using properties or array indexes of all object or array values
     *
     * If $valueKey is NULL, the complete arrays or objects are mapped to each index.
     *
     * @return Map<array-key, mixed>
     */
    function mapColumn(int|string $indexKey, int|string|null $valueKey): Map
    {
        return new Map(\array_column($this->values, $valueKey, $indexKey));
    }

    /**
     * Filter values using the given callback
     *
     * The callback should return TRUE to accept a value and FALSE to reject it.
     *
     * Returns a new collection with all accepted values.
     *
     * @param callable(T):bool $filter
     * @return static<T>
     */
    function filter(callable $filter): self
    {
        return new static(
            \count($this->values) > 0
                ? \array_filter($this->values, $filter)
                : []
        );
    }

    /**
     * Apply the callback to all values
     *
     * Returns a new collection with the modified values.
     *
     * @template TNext
     *
     * @param callable(T):TNext $callback
     * @return static<TNext>
     */
    function apply(callable $callback): self
    {
        return new static(
            \count($this->values) > 0
                ? \array_map($callback, $this->values)
                : []
        );
    }

    /**
     * Convert the collection to a map
     *
     * The callback should return a key for each given value.
     *
     * If the same key is returned multiple times, only the last returned value will be used.
     *
     * @template TMappedKey of array-key
     *
     * @param callable(T):TMappedKey $mapper
     * @return Map<TMappedKey, T>
     */
    function map(callable $mapper): Map
    {
        return Map::map($this->values, $mapper);
    }

    /**
     * Merge the collection with the given iterables
     *
     * Returns a new collection with the merged values.
     *
     * @param iterable<T> ...$iterables
     * @return static<T>
     */
    function merge(iterable ...$iterables): self
    {
        if (\count($iterables) === 0) {
            return clone $this;
        }

        $values = $this->values;

        foreach ($iterables as $iterable) {
            foreach ($iterable as $value) {
                $values[] = $value;
            }
        }

        return new static($values);
    }

    /**
     * Compute an intersection with the given iterables
     *
     * Values are converted to strings before the comparison.
     *
     * Returns a new collection containing all values of this collection that are also present in all the given iterables.
     *
     * @param iterable<T> ...$iterables
     * @return static<T>
     */
    function intersect(iterable ...$iterables): self
    {
        if (\count($this->values) === 0 || \count($iterables) === 0) {
            return new static();
        }

        return new static(\array_values(\array_intersect($this->values, ...IterableConverter::toLists($iterables))));
    }

    /**
     * Compute an intersection with the given iterables using a custom comparator
     *
     * The comparator must return an integer less than, equal to, or greater than zero if the first argument
     * is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new collection containing all values of this collection that are also present in all the given iterables.
     *
     * @template TOther
     *
     * @param callable(T|TOther, T|TOther):int $comparator
     * @param iterable<TOther> ...$iterables
     * @return static<T>
     */
    function intersectUsing(callable $comparator, iterable ...$iterables): self
    {
        if (\count($this->values) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toLists($iterables);
        $args[] = $comparator;

        // @phpstan-ignore argument.type (works with a single array)
        return new static(\array_values(\array_uintersect($this->values, ...$args)));
    }

    /**
     * Compute a difference between this collection and the given iterables
     *
     * Values are converted to strings before the comparison.
     *
     * Returns a new collection containing all values of this collection that are not present in any of the given iterables.
     *
     * @param iterable<T> ...$iterables
     * @return static<T>
     */
    function diff(iterable ...$iterables): self
    {
        if (\count($this->values) === 0 || \count($iterables) === 0) {
            return new static();
        }

        return new static(\array_values(\array_diff($this->values, ...IterableConverter::toLists($iterables))));
    }

    /**
     * Compute a difference between this collection and the given iterables using a custom comparator
     *
     * The comparator must return an integer less than, equal to, or greater than zero if the first argument
     * is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new collection containing all values of this collection that are not present in any of the given iterables.
     *
     * @template TOther
     *
     * @param callable(T|TOther, T|TOther):int $comparator
     * @param iterable<TOther> ...$iterables
     * @return static<T>
     */
    function diffUsing(callable $comparator, iterable ...$iterables): self
    {
        if (\count($this->values) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toLists($iterables);
        $args[] = $comparator;

        // @phpstan-ignore argument.type (works with a single array)
        return new static(\array_values(\array_udiff($this->values, ...$args)));
    }

    /**
     * Sort the collection
     *
     * Returns a new sorted collection.
     *
     * @see \SORT_REGULAR compare items normally (don't change types)
     * @see \SORT_NUMERIC compare items numerically
     * @see \SORT_STRING compare items as strings
     * @see \SORT_LOCALE_STRING compare items as strings based on the current locale
     * @see \SORT_NATURAL compare items as strings using "natural ordering" like natsort()
     * @see \SORT_FLAG_CASE can be combined (bitwise OR) with SORT_STRING or SORT_NATURAL to sort strings case-insensitively
     *
     * @return static<T>
     */
    function sort(int $flags = \SORT_REGULAR, bool $reverse = false): self
    {
        if (\count($this->values) === 0) {
            return new static();
        }

        $values = $this->values;

        if ($reverse) {
            \rsort($values, $flags);
        } else {
            \sort($values, $flags);
        }

        return new static($values);
    }

    /**
     * Sort the collection using a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first value is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new sorted collection.
     *
     * @param callable(T, T):int $comparator
     * @return static<T>
     */
    function sortBy(callable $comparator): self
    {
        if (\count($this->values) === 0) {
            return new static();
        }

        $values = $this->values;
        \usort($values, $comparator);

        return new static($values);
    }

    function count(): int
    {
        return \count($this->values);
    }

    /**
     * @param int $offset
     */
    function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->values);
    }

    /**
     * @param int $offset
     * @return T|null
     */
    function offsetGet(mixed $offset): mixed
    {
        return $this->values[$offset] ?? null;
    }

    /**
     * @param int|null $offset
     * @param T $value
     */
    function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->values[] = $value;
        } else {
            $this->set($offset, $value);
        }
    }

    /**
     * @param int $offset
     */
    function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * @return \Traversable<non-negative-int, T>
     */
    function getIterator(): \Traversable
    {
        /** @var \ArrayIterator<non-negative-int, T> */
        return new \ArrayIterator($this->values);
    }
}
