<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\Maybe\Maybe;

/**
 * @template-covariant T
 * @extends \IteratorAggregate<non-negative-int, T>
 */
interface ReadableList extends \Countable, \IteratorAggregate
{
    /**
     * Get all values as an array
     *
     * @return list<T>
     */
    function toArray(): array;

    /**
     * Cast the collection into a different subtype
     *
     * @template TType of self
     *
     * @param class-string<TType> $type
     * @return TType
     */
    function as(string $type): self;

    /**
     * Cast the list into a list of scalars
     *
     * @psalm-if-this-is self<scalar>
     *
     * @return ReadableScalarList<T>
     */
    function asScalars(): ReadableScalarList;

    /**
     * Cast the list into a list of objects
     *
     * @psalm-if-this-is self<object>
     *
     * @return ReadableObjectList<T>
     */
    function asObjects(): ReadableObjectList;

    /**
     * Cast the list into a list of arrays
     *
     * @psalm-if-this-is self<array>
     *
     * @return ReadableArrayList<T>
     */
    function asArrays(): ReadableArrayList;

    /**
     * See if the collection is empty
     */
    function isEmpty(): bool;

    /**
     * See if the given index exists
     */
    function has(int $index): bool;

    /**
     * See if the given value exists
     */
    function contains(mixed $value): bool;

    /**
     * Try to find the first occurrence of a value
     *
     * @return Maybe<non-negative-int> the found index
     */
    function find(mixed $value): Maybe;

    /**
     * Try to find the first occurrence of a value accepted by the filter
     *
     * @param callable(T):bool $filter
     * @return Maybe<non-negative-int>
     */
    function findUsing(callable $filter): Maybe;

    /**
     * Get value at the given index
     *
     * @param int $index
     * @return Maybe<T>
     */
    function get(int $index): Maybe;

    /**
     * Get the first value
     *
     * @return Maybe<T>
     */
    function first(): Maybe;

    /**
     * Get the last value
     *
     * @return Maybe<T>
     */
    function last(): Maybe;

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
    function reduce(callable $reducer, mixed $initial = null): mixed;

    /**
     * Extract a slice of the collection
     *
     * Both $index and $length can be negative, in which case they are relative to the end of the collection.
     *
     * @return static<T>
     */
    function slice(int $index, ?int $length = null): static;

    /**
     * Split the collection into chunks of the given size
     *
     * The last chunk might be smaller if collection size is not a multiple of $size.
     *
     * @param positive-int $size
     * @return ReadableObjectList<static<T>>
     */
    function chunk(int $size): ReadableObjectList;

    /**
     * Split the collection into the given number of chunks
     *
     * The last chunk might be smaller if collection size is not a multiple of $size.
     *
     * @return ReadableObjectList<static<T>>
     */
    function split(int $number): ReadableObjectList;

    /**
     * Reverse the collection
     *
     * Returns a new collection with values in reverse order.
     *
     * @return static<T>
     */
    function reverse(): static;

    /**
     * Get values in random order
     *
     * Returns a new collection with values in random order.
     *
     * @return static<T>
     */
    function shuffle(): static;

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
    function random(int $count): static;

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
    function filter(callable $filter): static;

    /**
     * Apply the callback to all values
     *
     * Returns a new collection with the modified values.
     *
     * @template TNext
     *
     * @param callable(T):TNext $callback
     * @return self<TNext>
     */
    function apply(callable $callback): self;

    /**
     * Call the callback with each value
     *
     * The callback's return value is ignored.
     *
     * @param callable(T):mixed $callback
     */
    function walk(callable $callback): void;

    /**
     * Merge the collection with the given iterables
     *
     * Returns a new collection with the merged values.
     *
     * @template TOther
     *
     * @param iterable<TOther> ...$iterables
     * @return self<T|TOther>
     */
    function merge(iterable ...$iterables): self;

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
    function intersectUsing(callable $comparator, iterable ...$iterables): static;

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
    function diffUsing(callable $comparator, iterable ...$iterables): static;

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
    function sortBy(callable $comparator): static;

    /**
     * Group values using a callback
     *
     * The callback should accept 2 arguments (index and value) and return a group key for each value.
     *
     * @template TGroupKey of array-key
     *
     * @param callable(non-negative-int, T):TGroupKey $grouper
     * @return ReadableObjectMap<TGroupKey, static<T>>
     */
    function group(callable $grouper): ReadableObjectMap;

    /**
     * Map the collection's values using a callback
     *
     * The callback should accept 2 arguments (index and value) and return a new key.
     *
     * If the same key is returned multiple times, only the last occurrence will be kept.
     *
     * @template TMappedKey of array-key
     *
     * @param callable(non-negative-int, T):TMappedKey $mapper
     * @return ReadableMap<TMappedKey, T>
     */
    function map(callable $mapper): ReadableMap;

    /**
     * Build a map from the collection's values using a callback
     *
     * The callback should accept 2 arguments (index and value) and return new key => value pairs.
     *
     * If the same key is returned multiple times, only the last pair will be used.
     *
     * @template TMappedKey of array-key
     * @template TMappedValue
     *
     * @param callable(non-negative-int, T):iterable<TMappedKey, TMappedValue> $builder
     * @return ReadableMap<TMappedKey, TMappedValue>
     */
    function buildMap(callable $builder): ReadableMap;

    /**
     * Convert the collection into a map directly
     *
     * @return ReadableMap<non-negative-int, T>
     */
    function toMap(): ReadableMap;

    /**
     * Get the number of values
     *
     * @return non-negative-int
     */
    function count(): int;

    /**
     * @return \Traversable<non-negative-int, T>
     */
    function getIterator(): \Traversable;
}
