<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\Iterable\IterableConverter;
use Kuria\Maybe\{Maybe, Some, None};

/**
 * Key-value map
 *
 * @template TKey of array-key
 * @template TValue
 * @implements \ArrayAccess<TKey, TValue>
 * @implements \IteratorAggregate<TKey, TValue>
 * @phpstan-consistent-constructor
 */
class Map implements \Countable, \ArrayAccess, \IteratorAggregate
{
    /** @var array<TKey, TValue> */
    private array $pairs;

    /**
     * @param array<TKey, TValue> $pairs
     */
    function __construct(array $pairs = [])
    {
        $this->pairs = $pairs;
    }

    /**
     * Create a map from an iterable
     *
     * @template TInputKey of array-key
     * @template TInputValue
     *
     * @param iterable<TInputKey, TInputValue> $pairs
     * @return static<TInputKey, TInputValue>
     */
    static function create(iterable $pairs = []): self
    {
        return new static(IterableConverter::toArray($pairs));
    }

    /**
     * Map values of the given iterable using a callback
     *
     * The callback should return a key for each given value.
     *
     * If the same key is returned multiple times, only the last returned value will be used.
     *
     * @template TInputValue
     * @template TMappedKey of array-key
     *
     * @param iterable<TInputValue> $iterable
     * @param callable(TInputValue):TMappedKey $mapper
     * @return static<TMappedKey, TInputValue>
     */
    static function map(iterable $iterable, callable $mapper): self
    {
        $pairs = [];

        foreach ($iterable as $v) {
            $pairs[$mapper($v)] = $v;
        }

        return new static($pairs);
    }

    /**
     * Build a map from an iterable using a callback
     *
     * The callback should return key => value pairs for each given key and value.
     *
     * If the same key is returned multiple times, only the first returned pair with that key will be used.
     *
     * @template TInputKey
     * @template TInputValue
     * @template TMappedKey of array-key
     * @template TMappedValue
     *
     * @param iterable<TInputKey, TInputValue> $iterable
     * @param callable(TInputKey, TInputValue):array<TMappedKey, TMappedValue> $mapper
     * @return static<TMappedKey, TMappedValue>
     */
    static function build(iterable $iterable, callable $mapper): self
    {
        $pairs = [];

        foreach ($iterable as $k => $v) {
            $pairs += $mapper($k, $v);
        }

        return new static($pairs);
    }

    /**
     * Combine a list of keys and a list of values to create a map
     *
     * Both lists must have the same number of elements. Keys must be scalar.
     * 
     * @template TInputKey of array-key
     * @template TInputValue
     * 
     * @param iterable<TInputKey> $keys
     * @param iterable<TInputValue> $values
     * @return static<TInputKey, TInputValue>
     */
    static function combine(iterable $keys, iterable $values): self
    {
        return new static(\array_combine(IterableConverter::toList($keys), IterableConverter::toList($values)));
    }

    /**
     * Replace all pairs with the given iterable
     *
     * @param iterable<TKey, TValue> $pairs
     */
    function setPairs(iterable $pairs): void
    {
        $this->pairs = IterableConverter::toArray($pairs);
    }

    /**
     * Get the pairs as an array
     *
     * @return array<TKey, TValue>
     */
    function toArray(): array
    {
        return $this->pairs;
    }

    /**
     * See if the map is empty
     */
    function isEmpty(): bool
    {
        return \count($this->pairs) === 0;
    }

    /**
     * See if the given key exists
     *
     * @param TKey $key
     */
    function has(mixed $key): bool
    {
        return \array_key_exists($key, $this->pairs);
    }

    /**
     * See if the given value exists
     *
     * @param TValue $value
     */
    function contains($value): bool
    {
        return \in_array($value, $this->pairs, true);
    }

    /**
     * Try to find the first occurrence of a value
     *
     * @param TValue $value
     * @return Maybe<TKey> the found key
     */
    function find(mixed $value): Maybe
    {
        $key = \array_search($value, $this->pairs, true);

        return $key !== false ? new Some($key) : new None();
    }

    /**
     * Try to find the first occurrence of a value accepted by the filter
     *
     * @param callable(TValue):bool $filter
     * @return Maybe<TKey>
     */
    function findUsing(callable $filter): Maybe
    {
        foreach ($this->pairs as $key => $value) {
            if ($filter($value)) {
                return new Some($key);
            }
        }

        return new None();
    }

    /**
     * Get value for the given key
     *
     * @param TKey $key
     * @return Maybe<TValue>
     */
    function get(mixed $key): Maybe
    {
        return \array_key_exists($key, $this->pairs) ? new Some($this->pairs[$key]) : new None();
    }

    /**
     * Get all values
     *
     * @return Collection<TValue>
     */
    function values(): Collection
    {
        return new Collection(\array_values($this->pairs));
    }

    /**
     * Get all keys
     *
     * @return Collection<TKey>
     */
    function keys(): Collection
    {
        return new Collection(\array_keys($this->pairs));
    }

    /**
     * Define a pair
     *
     * @param TKey $key
     * @param TValue $value
     */
    function set(mixed $key, mixed $value): void
    {
        $this->pairs[$key] = $value;
    }

    /**
     * Add pairs from other iterables to this map
     *
     * If the same key exists in multiple iterables, the last value will be used.
     *
     * @param iterable<TKey, TValue> ...$iterables
     */
    function add(iterable ...$iterables): void
    {
        foreach ($iterables as $iterable) {
            foreach ($iterable as $k => $v) {
                $this->pairs[$k] = $v;
            }
        }
    }

    /**
     * Fill specific keys with a value
     *
     * @param iterable<TKey> $keys
     * @param TValue $value
     */
    function fill(iterable $keys, mixed $value): void
    {
        foreach ($keys as $k) {
            $this->pairs[$k] = $value;
        }
    }

    /**
     * Remove pairs with the given keys
     *
     * @param TKey ...$keys
     */
    function remove(mixed ...$keys): void
    {
        foreach ($keys as $k) {
            unset($this->pairs[$k]);
        }
    }

    /**
     * Remove all pairs
     */
    function clear(): void
    {
        $this->pairs = [];
    }

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
    function reduce(callable $reducer, mixed $initial = null): mixed
    {
        $result = $initial;

        foreach ($this->pairs as $key => $value) {
            $result = $reducer($result, $key, $value);
        }

        return $result;
    }

    /**
     * Swap keys and values
     *
     * The values must be scalar or convertable to a string.
     *
     * @return static<array-key, TKey>
     */
    function flip(): self
    {
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            $pairs[(string) $v] = $k;
        }

        return new static($pairs);
    }

    /**
     * Randomize pair order
     *
     * Returns a new map with pairs in random order.
     *
     * @return static<TKey, TValue>
     */
    function shuffle(): self
    {
        $keys = \array_keys($this->pairs);
        $values = $this->pairs;

        \shuffle($values);

        return new static(\array_combine($keys, $values));
    }

    /**
     * Gather values from properties or array keys of all object or array values
     *
     * Returns a new map with the gathered values. Preserves original keys if $indexKey is NULL.
     *
     * @return static<array-key, mixed>
     */
    function column(int|string $key, int|string|null $indexKey = null): self
    {
        if ($indexKey !== null) {
            return new static(\array_column($this->pairs, $key, $indexKey));
        }

        // cannot use \array_column() here because it does not preserve keys
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            if (\is_array($v)) {
                if (\array_key_exists($key, $v)) {
                    $pairs[$k] = $v[$key];
                }
            } elseif (\is_object($v)) {
                if (
                    isset($v->{$key})
                    || \property_exists($v, (string) $key) && (new \ReflectionProperty($v, (string) $key))->isPublic()
                ) {
                    $pairs[$k] = $v->{$key};
                }
            }
        }

        return new static($pairs);
    }

    /**
     * Gather keys from properties or array keys of all object or array values
     *
     * Returns a new map with the gathered keys and existing values.
     *
     * @return static<array-key, TValue>
     */
    function indexBy(int|string $key): self
    {
        return new static(\array_column($this->pairs, null, $key));
    }

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
    function filter(callable $filter): self
    {
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            if ($filter($k, $v)) {
                $pairs[$k] = $v;
            }
        }

        return new static($pairs);
    }

    /**
     * Apply the callback to all pairs
     *
     * Returns a new map with the modified values.
     *
     * @template TNextValue
     *
     * @param callable(TKey, TValue):TNextValue $callback
     * @return static<TKey, TNextValue>
     */
    function apply(callable $callback): self
    {
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            $pairs[$k] = $callback($k, $v);
        }

        return new Map($pairs);
    }

    /**
     * Remap pairs using the given callback
     *
     * The callback should accept 2 arguments (key and value) and return new key => value pairs.
     *
     * If the same key is returned multiple times, only the first returned pair with that key will be used.
     *
     * Returns a new map with the returned pairs.
     *
     * @template TNextKey of array-key
     * @template TNextValue
     *
     * @param callable(TKey, TValue):array<TNextKey, TNextValue> $mapper
     * @return static<TNextKey, TNextValue>
     */
    function remap(callable $mapper): self
    {
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            $pairs += $mapper($k, $v);
        }

        return new static($pairs);
    }

    /**
     * Merge the map with the given iterables
     *
     * If the same key exists in multiple iterables, the last given value will be used.
     *
     * Returns a new map with the merged pairs.
     *
     * @param iterable<TKey, TValue> ...$iterables
     * @return static<TKey, TValue>
     */
    function merge(iterable ...$iterables): self
    {
        if (\count($iterables) === 0) {
            return clone $this;
        }

        return new static(\array_replace($this->pairs, ...IterableConverter::toArrays($iterables)));
    }

    /**
     * Compute an intersection with the given iterables
     *
     * Values are converted to a string before the comparison.
     *
     * Returns a new map containing all pairs of this map that are also present in all the given iterables.
     *
     * @param iterable<array-key, TValue> ...$iterables
     * @return static<TKey, TValue>
     */
    function intersect(iterable ...$iterables): self
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        return new static(\array_intersect_assoc($this->pairs, ...IterableConverter::toArrays($iterables)));
    }

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
    function intersectUsing(callable $comparator, iterable ...$iterables): self
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toArrays($iterables);
        $args[] = $comparator;

        // @phpstan-ignore argument.type (works with a single array)
        return new static(\array_uintersect_assoc($this->pairs, ...$args));
    }

    /**
     * Compute a key intersection with the given iterables
     *
     * Returns a new map containing all pairs of this map whose keys are also present in all the given iterables.
     *
     * @param iterable<array-key, mixed> ...$iterables
     * @return static<TKey, TValue>
     */
    function intersectKeys(iterable ...$iterables): self
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        return new static(\array_intersect_key($this->pairs, ...IterableConverter::toArrays($iterables)));
    }

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
    function intersectKeysUsing(callable $comparator, iterable ...$iterables): self
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toArrays($iterables);
        $args[] = $comparator;

        // @phpstan-ignore argument.type (works with a single array)
        return new static(\array_intersect_ukey($this->pairs, ...$args));
    }

    /**
     * Compute a difference between this map and the given iterables
     *
     * Values are converted to a string before the comparison.
     *
     * Returns a new map containing all pairs of this map that are not present in any of the given iterables.
     *
     * @param iterable<array-key, TValue> ...$iterables
     * @return static<TKey, TValue>
     */
    function diff(iterable ...$iterables): self
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        return new static(\array_diff_assoc($this->pairs, ...IterableConverter::toArrays($iterables)));
    }

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
    function diffUsing(callable $comparator, iterable ...$iterables): self
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toArrays($iterables);
        $args[] = $comparator;

        // @phpstan-ignore argument.type (works with a single array)
        return new static(\array_udiff_assoc($this->pairs, ...$args));
    }

    /**
     * Compute a key difference between this map and the given iterables
     *
     * Returns a new map containing all pairs of this map whose keys are not present in all the given iterables.
     *
     * @param iterable<array-key, mixed> ...$iterables
     * @return static<TKey, TValue>
     */
    function diffKeys(iterable ...$iterables): self
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        return new static(\array_diff_key($this->pairs, ...IterableConverter::toArrays($iterables)));
    }

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
    function diffKeysUsing(callable $comparator, iterable ...$iterables): self
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toArrays($iterables);
        $args[] = $comparator;

        // @phpstan-ignore argument.type (works with a single array)
        return new static(\array_diff_ukey($this->pairs, ...$args));
    }

    /**
     * Sort the map using its values
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
    function sort(int $flags = SORT_REGULAR, bool $reverse = false): self
    {
        if (\count($this->pairs) === 0) {
            return new static();
        }

        $pairs = $this->pairs;

        if ($reverse) {
            \arsort($pairs, $flags);
        } else {
            \asort($pairs, $flags);
        }

        return new static($pairs);
    }

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
    function sortBy(callable $comparator): self
    {
        if (\count($this->pairs) === 0) {
            return new static();
        }

        $pairs = $this->pairs;
        \uasort($pairs, $comparator);

        return new static($pairs);
    }

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
    function sortKeys(int $flags = SORT_REGULAR, bool $reverse = false): self
    {
        if (\count($this->pairs) === 0) {
            return new static();
        }

        $pairs = $this->pairs;

        if ($reverse) {
            \krsort($pairs, $flags);
        } else {
            \ksort($pairs, $flags);
        }

        return new static($pairs);
    }

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
    function sortKeysBy(callable $comparator): self
    {
        if (\count($this->pairs) === 0) {
            return new static();
        }

        $pairs = $this->pairs;
        \uksort($pairs, $comparator);

        return new static($pairs);
    }

    function count(): int
    {
        return count($this->pairs);
    }

    /**
     * @param TKey $offset
     * @return bool
     */
    function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->pairs);
    }

    /**
     * @param TKey $offset
     * @return TValue|null
     */
    function offsetGet(mixed $offset): mixed
    {
        return $this->pairs[$offset] ?? null;
    }

    /**
     * @param TKey|null $offset
     * @param TValue $value
     */
    function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->pairs[] = $value;
        } else {
            $this->pairs[$offset] = $value;
        }
    }

    /**
     * @param TKey $offset
     */
    function offsetUnset(mixed $offset): void
    {
        unset($this->pairs[$offset]);
    }

    /**
     * @return \Traversable<TKey, TValue>
     */
    function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->pairs);
    }
}
