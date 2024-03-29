<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\Iterable\IterableConverter;
use Kuria\Maybe\{Maybe, Some, None};

/**
 * Sequential list of values
 *
 * @template T
 * @implements ReadableList<T>
 * @implements \ArrayAccess<int, T>
 *
 * @psalm-consistent-constructor
 * @phpstan-consistent-constructor
 */
class Collection implements ReadableList, \ArrayAccess
{
    /**
     * @param list<T> $values
     */
    function __construct(protected array $values = [])
    {}

    /**
     * Create a collection from an iterable
     *
     * @template TValue of T
     *
     * @param iterable<TValue> $values
     * @return static<TValue>
     */
    static function fromIterable(iterable $values = []): static
    {
        return new static(IterableConverter::toList($values));
    }

    /**
     * Create a collection from the passed arguments
     *
     * @template TValue of T
     *
     * @param TValue ...$values
     * @return static<TValue>
     */
    static function collect(mixed ...$values): static
    {
        return new static(IterableConverter::toList($values));
    }

    function toArray(): array
    {
        return $this->values;
    }

    function as(string $type): ReadableList
    {
        return new $type($this->values);
    }

    /**
     * @return ScalarList<T>
     */
    function asScalars(): ScalarList
    {
        return new ScalarList($this->values);
    }

    /**
     * @return ObjectList<T>
     */
    function asObjects(): ObjectList
    {
        return new ObjectList($this->values);
    }

    /**
     * @return ArrayList<T>
     */
    function asArrays(): ArrayList
    {
        return new ArrayList($this->values);
    }

    function isEmpty(): bool
    {
        return \count($this->values) === 0;
    }

    function has(int $index): bool
    {
        return \array_key_exists($index, $this->values);
    }

    function contains(mixed $value): bool
    {
        return \in_array($value, $this->values, true);
    }

    function find(mixed $value): Maybe
    {
        $index = \array_search($value, $this->values, true);

        return $index !== false ? new Some($index) : new None();
    }

    function findUsing(callable $filter): Maybe
    {
        foreach ($this->values as $i => $v) {
            if ($filter($v)) {
                return new Some($i);
            }
        }

        return new None();
    }

    function get(int $index): Maybe
    {
        return \array_key_exists($index, $this->values) ? new Some($this->values[$index]) : new None();
    }

    function first(): Maybe
    {
        return \count($this->values) > 0 ? new Some($this->values[0]) : new None();
    }

    function last(): Maybe
    {
        $count = \count($this->values);

        return $count > 0 ? new Some($this->values[$count - 1]) : new None();
    }

    /**
     * Set a value at the given index
     *
     * The index must point to an existing value or the end of the collection.
     *
     * @param T $value
     */
    function set(int $index, mixed $value): void
    {
        if (\array_key_exists($index, $this->values)) {
            $this->values[$index] = $value;
        } elseif ($index === \count($this->values)) {
            $this->values[] = $value;
        } else {
            throw new \OutOfRangeException(\sprintf('Index %d is out of bounds (0 - %d)', $index, \count($this->values)));
        }
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
     * Push one or more values onto the end of the collection
     *
     * @param T ...$values
     */
    function push(mixed ...$values): void
    {
        if (!\array_is_list($values)) {
            $values = \array_values($values);
        }

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
     * Shift a value off the beginning of the collection
     *
     * @return Maybe<T>
     */
    function shift(): Maybe
    {
        return \count($this->values) > 0 ? new Some(\array_shift($this->values)) : new None();
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
        if (!\array_is_list($values)) {
            $values = \array_values($values);
        }

        \array_unshift($this->values, ...$values);
    }

    /**
     * Push values from the given iterable onto the end of the collection
     *
     * @param iterable<T> $values
     */
    function add(iterable $values): void
    {
        foreach ($values as $v) {
            $this->values[] = $v;
        }
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
            /** @psalm-suppress PropertyTypeCoercion false-positive */
            \array_splice($this->values, $index, 0, $values);
        }
    }

    /**
     * Remove or replace a part of the collection
     *
     * Both $index and $length can be negative, in which case they are relative to the end of the collection.
     *
     * If $length is NULL, all elements until the end of the collection are removed or replaced.
     *
     * @param iterable<T>|null $replacement
     */
    function splice(int $index, ?int $length = null, ?iterable $replacement = null): void
    {
        \array_splice(
            $this->values,
            $index,
            $length ?? count($this->values),
            $replacement !== null ? IterableConverter::toList($replacement) : [],
        );
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

    function reduce(callable $reducer, mixed $initial = null): mixed
    {
        return \array_reduce($this->values, $reducer, $initial);
    }

    function slice(int $index, ?int $length = null): static
    {
        return new static(\array_slice($this->values, $index, $length));
    }

    /**
     * @return ObjectList<static<T>>
     */
    function chunk(int $size): ObjectList
    {
        /** @var ObjectList<static<T>> $chunks */
        $chunks = new ObjectList();

        foreach (\array_chunk($this->values, $size) as $chunk) {
            /** @psalm-suppress InvalidArgument https://github.com/vimeo/psalm/issues/10854 */
            $chunks->push(new static($chunk));
        }

        return $chunks;
    }

    /**
     * @return ObjectList<static<T>>
     */
    function split(int $number): ObjectList
    {
        $count = \count($this->values);

        if ($count === 0 || $number < 1) {
            /** @var ObjectList<static<T>> */
            return new ObjectList();
        }

        /** @psalm-suppress ArgumentTypeCoercion size is positive */
        return $this->chunk((int) \ceil($count / $number));
    }

    function reverse(): static
    {
        return new static(\array_reverse($this->values));
    }

    function shuffle(): static
    {
        $values = $this->values;
        \shuffle($values);

        return new static($values);
    }

    function random(int $count): static
    {
        if ($count <= 0 || \count($this->values) === 0) {
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

    function filter(callable $filter): static
    {
        return new static(
            \count($this->values) > 0
                ? \array_values(\array_filter($this->values, $filter))
                : []
        );
    }

    /**
     * @template TNext
     *
     * @param callable(T):TNext $callback
     * @return self<TNext>
     */
    function apply(callable $callback): self
    {
        return new self(
            \count($this->values) > 0
                ? \array_map($callback, $this->values)
                : []
        );
    }

    function walk(callable $callback): void
    {
        foreach ($this->values as $value) {
            $callback($value);
        }
    }

    /**
     * @template TOther
     *
     * @param iterable<TOther> ...$iterables
     * @return self<T|TOther>
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

        return new self($values);
    }

    function intersectUsing(callable $comparator, iterable ...$iterables): static
    {
        if (\count($this->values) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toLists($iterables);
        $args[] = $comparator;

        return new static(\array_values(\array_uintersect($this->values, ...$args)));
    }

    function diffUsing(callable $comparator, iterable ...$iterables): static
    {
        if (\count($this->values) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toLists($iterables);
        $args[] = $comparator;

        return new static(\array_values(\array_udiff($this->values, ...$args)));
    }

    function sortBy(callable $comparator): static
    {
        if (\count($this->values) === 0) {
            return new static();
        }

        $values = $this->values;
        \usort($values, $comparator);

        return new static($values);
    }

    /**
     * @template TGroupKey of array-key
     *
     * @param callable(non-negative-int, T):TGroupKey $grouper
     * @return ObjectMap<TGroupKey, static<T>>
     */
    function group(callable $grouper): ObjectMap
    {
        /** @var ObjectMap<TGroupKey, static<T>> $groups */
        $groups = new ObjectMap();

        foreach ($this->values as $i => $v) {
            /** @psalm-suppress PossiblyNullArgument,PossiblyNullReference https://github.com/vimeo/psalm/issues/10857 */
            ($groups[$grouper($i, $v)] ??= new static())->push($v);
        }

        return $groups;
    }

    /**
     * @template TMappedKey of array-key
     *
     * @param callable(non-negative-int, T):TMappedKey $mapper
     * @return Map<TMappedKey, T>
     */
    function map(callable $mapper): Map
    {
        return $this->toMap()->remap($mapper);
    }

    /**
     * @template TMappedKey of array-key
     * @template TMappedValue
     *
     * @param callable(non-negative-int, T):iterable<TMappedKey, TMappedValue> $builder
     * @return Map<TMappedKey, TMappedValue>
     */
    function buildMap(callable $builder): Map
    {
        return $this->toMap()->rebuild($builder);
    }

    /**
     * @return Map<non-negative-int, T>
     */
    function toMap(): Map
    {
        /** @var Map<non-negative-int, T> */
        return new Map($this->values);
    }

    function count(): int
    {
        return \count($this->values);
    }

    function getIterator(): \Traversable
    {
        /** @var \ArrayIterator<non-negative-int, T> */
        return new \ArrayIterator($this->values);
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
}
