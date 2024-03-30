<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\Maybe\Maybe;
use Random\Randomizer;

/**
 * @template-covariant TKey of array-key
 * @template-covariant TValue
 * @extends Structure<TKey, TValue>
 */
interface ReadableMap extends Structure
{
    /**
     * Cast the map into a different subtype
     *
     * @template TType of self<TKey, TValue>
     *
     * @param class-string<TType> $type
     * @return TType
     */
    function as(string $type): self;

    /**
     * Cast the map into a scalar map
     *
     * @psalm-if-this-is self<array-key, scalar>
     *
     * @return ReadableScalarMap<TKey, TValue>
     */
    function asScalars(): ReadableScalarMap;

    /**
     * Cast the map into an object map
     *
     * @psalm-if-this-is self<array-key, object>
     *
     * @return ReadableObjectMap<TKey, TValue>
     */
    function asObjects(): ReadableObjectMap;

    /**
     * Cast the map into an array map
     *
     * @psalm-if-this-is self<array-key, object>
     *
     * @return ReadableArrayMap<TKey, TValue>
     */
    function asArrays(): ReadableArrayMap;

    /**
     * See if the given key exists
     */
    function has(int|string $key): bool;

    /**
     * See if the given value exists
     */
    function contains(mixed $value): bool;

    /**
     * Try to find the first occurrence of a value
     *
     * @return Maybe<TKey> the found key
     */
    function find(mixed $value): Maybe;

    /**
     * Try to find the first occurrence of a value accepted by the filter
     *
     * @param callable(TValue):bool $filter
     * @return Maybe<TKey>
     */
    function findUsing(callable $filter): Maybe;

    /**
     * Get value for the given key
     *
     * @return Maybe<TValue>
     */
    function get(int|string $key): Maybe;

    /**
     * Get the first value
     *
     * @return Maybe<TValue>
     */
    function first(): Maybe;

    /**
     * Get the last value
     *
     * @return Maybe<TValue>
     */
    function last(): Maybe;

    /**
     * Get the first key
     *
     * @return Maybe<TKey>
     */
    function firstKey(): Maybe;

    /**
     * Get the last key
     *
     * @return Maybe<TKey>
     */
    function lastKey(): Maybe;

    /**
     * Get a random value
     *
     * @return Maybe<TValue>
     */
    function random(?Randomizer $randomizer = null): Maybe;

    /**
     * Get a random key
     *
     * @return Maybe<TKey>
     */
    function randomKey(?Randomizer $randomizer = null): Maybe;

    /**
     * Get all keys
     *
     * @return ReadableScalarList<TKey>
     */
    function keys(): ReadableScalarList;

    /**
     * Get all values
     *
     * @return ReadableList<TValue>
     */
    function values(): ReadableList;

    /**
     * Reduce the map to a single value
     *
     * The callback should accept 3 arguments (iteration result and current key and value)
     * and return a new iteration result. The returned iteration result will be
     * used in subsequent callback invocations.
     *
     * Returns the final iteration result or $initial if the map is empty.
     *
     * @template TResult
     * @template TInitial
     *
     * @param callable(TResult|TInitial, TKey, TValue):TResult $reducer
     * @param TInitial $initial
     * @return TResult|TInitial
     */
    function reduce(callable $reducer, mixed $initial = null): mixed;

    /**
     * Extract a slice of the map
     *
     * Both $offset and $length can be negative, in which case they are relative to the end of the map.
     *
     * @return static<TKey, TValue>
     */
    function slice(int $offset, ?int $length = null): static;

    /**
     * Split the map into chunks of the given size
     *
     * - the last chunk might be smaller if map size is not a multiple of $size
     * - if $size is less than 1, an empty list will be returned
     *
     * @return ReadableObjectList<static<TKey, TValue>>
     */
    function chunk(int $size): ReadableObjectList;

    /**
     * Split the map into the given number of chunks
     *
     * - the last chunk might be smaller if map size is not a multiple of $size
     * - if $number is less than 1, an empty list will be returned
     *
     * @return ReadableObjectList<static<TKey, TValue>>
     */
    function split(int $number): ReadableObjectList;

    /**
     * Reverse the map
     *
     * Returns a new map with pairs in reverse order.
     *
     * @return static<TKey, TValue>
     */
    function reverse(): static;

    /**
     * Shuffle the map
     *
     * Returns a new map with pairs in random order.
     *
     * @return static<TKey, TValue>
     */
    function shuffle(?Randomizer $randomizer = null): static;

    /**
     * Pick N random pairs from the map
     *
     * - the pairs keep their original order
     * - if $num is greater than the size of the map, all pairs will be returned
     * - if $num is less than 1, an empty map will be returned
     *
     * Returns a new map with the randomly chosen values.
     *
     * @return static<TKey, TValue>
     */
    function pick(int $num, ?Randomizer $randomizer = null): static;

    /**
     * Filter pairs using the given callback
     *
     * The callback should accept 2 arguments (key and value) return TRUE to accept a pair and FALSE to reject it.
     *
     * Returns a new map with all accepted pairs.
     *
     * @param callable(TKey, TValue):bool $filter
     * @return static<TKey, TValue>
     */
    function filter(callable $filter): static;

    /**
     * Apply the callback to all pairs
     *
     * Returns a new map with the modified values.
     *
     * @template TNextValue
     *
     * @param callable(TKey, TValue):TNextValue $callback
     * @return self<TKey, TNextValue>
     */
    function apply(callable $callback): self;

    /**
     * Call the callback with each pair
     *
     * The callback's return value is ignored.
     *
     * @param callable(TKey, TValue):mixed $callback
     */
    function walk(callable $callback): void;

    /**
     * Merge the map with the given iterables
     *
     * If the same key exists in multiple iterables, the last given value will be used.
     *
     * Returns a new map with the merged pairs.
     *
     * @template TOtherKey as array-key
     * @template TOtherValue
     *
     * @param iterable<TOtherKey, TOtherValue> ...$iterables
     * @return static<TKey|TOtherKey, TValue|TOtherValue>
     */
    function merge(iterable ...$iterables): static;

    /**
     * Compute an intersection with the given iterables using a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first value is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new map containing all pairs of this map that are also present in all the given iterables.
     *
     * @template TOtherValue
     *
     * @param callable(TValue|TOtherValue, TValue|TOtherValue):int $comparator
     * @param iterable<array-key, TOtherValue> ...$iterables
     * @return static<TKey, TValue>
     */
    function intersectUsing(callable $comparator, iterable ...$iterables): static;

    /**
     * Compute a key intersection with the given iterables
     *
     * Returns a new map containing all pairs of this map whose keys are also present in all the given iterables.
     *
     * @param iterable<array-key, mixed> ...$iterables
     * @return static<TKey, TValue>
     */
    function intersectKeys(iterable ...$iterables): static;

    /**
     * Compute a key intersection with the given iterables using a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first key is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new map containing all pairs of this map whose keys are also present in all the given iterables.
     *
     * @template TOtherKey of array-key
     *
     * @param callable(TKey|TOtherKey, TKey|TOtherKey):int $comparator
     * @param iterable<TOtherKey, mixed> ...$iterables
     * @return static<TKey, TValue>
     */
    function intersectKeysUsing(callable $comparator, iterable ...$iterables): static;

    /**
     * Compute a difference between this map and the given iterables using a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first value is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new map containing all pairs of this map that are not present in any of the given iterables.
     *
     * @template TOtherValue
     *
     * @param callable(TValue|TOtherValue, TValue|TOtherValue):int $comparator
     * @param iterable<array-key, TOtherValue> $iterables
     * @return static<TKey, TValue>
     */
    function diffUsing(callable $comparator, iterable ...$iterables): static;

    /**
     * Compute a key difference between this map and the given iterables
     *
     * Returns a new map containing all pairs of this map whose keys are not present in all the given iterables.
     *
     * @param iterable<array-key, mixed> ...$iterables
     * @return static<TKey, TValue>
     */
    function diffKeys(iterable ...$iterables): static;

    /**
     * Compute a key difference between this map and the given iterables using a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first key is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new map containing all pairs of this map whose keys are not present in all the given iterables.
     *
     * @template TOtherKey of array-key
     *
     * @param callable(TKey|TOtherKey, TKey|TOtherKey):int $comparator
     * @param iterable<TOtherKey, mixed> ...$iterables
     * @return static<TKey, TValue>
     */
    function diffKeysUsing(callable $comparator, iterable ...$iterables): static;

    /**
     * Sort the map using its values and a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first value is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new sorted map.
     *
     *
     * @param callable(TValue, TValue):int $comparator
     * @return static<TKey, TValue>
     */
    function sortBy(callable $comparator): static;

    /**
     * Sort the map using its keys
     *
     * Returns a new sorted map.
     *
     * @see \SORT_REGULAR compare items normally (don't change types)
     * @see \SORT_NUMERIC compare items numerically
     * @see \SORT_STRING compare items as strings
     * @see \SORT_LOCALE_STRING compare items as strings based on the current locale
     * @see \SORT_NATURAL compare items as strings using "natural ordering" like natsort()
     * @see \SORT_FLAG_CASE can be combined (bitwise OR) with SORT_STRING or SORT_NATURAL to sort strings case-insensitively
     *
     * @return static<TKey, TValue>
     */
    function sortKeys(int $flags = SORT_REGULAR, bool $reverse = false): static;

    /**
     * Sort the map using its keys and a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first value is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new sorted map.
     *
     * @param callable(TKey, TKey):int $comparator
     * @return static<TKey, TValue>
     */
    function sortKeysBy(callable $comparator): static;

    /**
     * Group pairs using a callback
     *
     * The callback should accept 2 arguments (key and value) and return a group key.
     *
     * Returns a new map with the grouped pairs.
     *
     * @template TGroupKey of array-key
     *
     * @param callable(TKey, TValue):TGroupKey $grouper
     * @return ReadableObjectMap<TGroupKey, static<TKey, TValue>>
     */
    function group(callable $grouper): ReadableObjectMap;

    /**
     * Map pairs to new keys using the given callback
     *
     * The callback should accept 2 arguments (key and value) and return a new key.
     *
     * If the same key is returned multiple times, only the last occurrence will be kept.
     *
     * Returns a new map with the returned keys.
     *
     * @template TMappedKey of array-key
     *
     * @param callable(TKey, TValue):TMappedKey $mapper
     * @return self<TMappedKey, TValue>
     */
    function remap(callable $mapper): self;

    /**
     * Rebuild the map using the given callback
     *
     * The callback should accept 2 arguments (key and value) and return new key => value pairs.
     *
     * If the same key is returned multiple times, only the last pair will be used.
     *
     * Returns a new map with the returned pairs.
     *
     * @template TMappedKey of array-key
     * @template TMappedValue
     *
     * @param callable(TKey, TValue):iterable<TMappedKey, TMappedValue> $builder
     * @return self<TMappedKey, TMappedValue>
     */
    function rebuild(callable $builder): self;
}
