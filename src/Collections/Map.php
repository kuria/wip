<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\Iterable\IterableConverter;
use Kuria\Maybe\{Maybe, Some, None};
use Random\Randomizer;

/**
 * Key-value map
 *
 * @template TKey of array-key
 * @template TValue
 * @implements ReadableMap<TKey, TValue>
 * @implements \ArrayAccess<array-key, TValue>
 *
 * @psalm-consistent-constructor
 * @phpstan-consistent-constructor
 */
class Map implements ReadableMap, \ArrayAccess
{
    /**
     * @param array<TKey, TValue> $pairs
     */
    function __construct(protected array $pairs = [])
    {}

    /**
     * Create a map from an iterable
     *
     * @template TInputKey of TKey
     * @template TInputValue of TValue
     *
     * @param iterable<TInputKey, TInputValue> $pairs
     * @return static<TInputKey, TInputValue>
     */
    static function fromIterable(iterable $pairs = []): static
    {
        return new static(IterableConverter::toArray($pairs));
    }

    /**
     * Combine a list of keys and a list of values to create a map
     *
     * Both lists must have the same number of elements. Keys must be scalar.
     * 
     * @template TInputKey of TKey
     * @template TInputValue of TValue
     * 
     * @param iterable<TInputKey> $keys
     * @param iterable<TInputValue> $values
     * @return static<TInputKey, TInputValue>
     */
    static function combine(iterable $keys, iterable $values): static
    {
        return new static(\array_combine(IterableConverter::toList($keys), IterableConverter::toList($values)));
    }

    function as(string $type): ReadableMap
    {
        return new $type($this->pairs);
    }

    /**
     * @return ScalarMap<TKey, TValue>
     */
    function asScalars(): ScalarMap
    {
        return new ScalarMap($this->pairs);
    }

    /**
     * @return ObjectMap<TKey, TValue>
     */
    function asObjects(): ObjectMap
    {
        return new ObjectMap($this->pairs);
    }

    /**
     * @return ArrayMap<TKey, TValue>
     */
    function asArrays(): ArrayMap
    {
        return new ArrayMap($this->pairs);
    }

    function toArray(): array
    {
        return $this->pairs;
    }

    function isEmpty(): bool
    {
        return \count($this->pairs) === 0;
    }

    function has(int|string $key): bool
    {
        return \array_key_exists($key, $this->pairs);
    }

    function contains(mixed $value): bool
    {
        return \in_array($value, $this->pairs, true);
    }

    function find(mixed $value): Maybe
    {
        $key = \array_search($value, $this->pairs, true);

        return $key !== false ? new Some($key) : new None();
    }

    function findUsing(callable $filter): Maybe
    {
        foreach ($this->pairs as $key => $value) {
            if ($filter($value)) {
                return new Some($key);
            }
        }

        return new None();
    }

    function get(int|string $key): Maybe
    {
        return \array_key_exists($key, $this->pairs) ? new Some($this->pairs[$key]) : new None();
    }

    function first(): Maybe
    {
        return \count($this->pairs) > 0
            ? new Some($this->pairs[\array_key_first($this->pairs)])
            : new None();
    }

    function last(): Maybe
    {
        return \count($this->pairs) > 0
            ? new Some($this->pairs[\array_key_last($this->pairs)])
            : new None();
    }

    function firstKey(): Maybe
    {
        return \count($this->pairs) > 0
            ? new Some(\array_key_first($this->pairs))
            : new None();
    }

    function lastKey(): Maybe
    {
        return \count($this->pairs) > 0
            ? new Some(\array_key_last($this->pairs))
            : new None();
    }

    function random(?Randomizer $randomizer = null): Maybe
    {
        $count = \count($this->pairs);

        if ($count > 0) {
            $randomizer ??= $this->getDefaultRandomizer();

            return new Some($this->pairs[$randomizer->pickArrayKeys($this->pairs, 1)[0]]);
        }

        return new None();
    }

    function randomKey(?Randomizer $randomizer = null): Maybe
    {
        $count = \count($this->pairs);

        if ($count > 0) {
            $randomizer ??= $this->getDefaultRandomizer();

            return new Some($randomizer->pickArrayKeys($this->pairs, 1)[0]);
        }

        return new None();
    }

    /**
     * @return ScalarList<TKey>
     */
    function keys(): ScalarList
    {
        return new ScalarList(\array_keys($this->pairs));
    }

    /**
     * @return Collection<TValue>
     */
    function values(): Collection
    {
        return new Collection(\array_values($this->pairs));
    }

    /**
     * Define a pair
     *
     * @param TKey $key
     * @param TValue $value
     */
    function set(int|string $key, mixed $value): void
    {
        $this->pairs[$key] = $value;
    }

    /**
     * Set multiple keys to the same value
     *
     * @param iterable<TKey> $keys
     * @param TValue $value
     */
    function setMultiple(iterable $keys, mixed $value): void
    {
        foreach ($keys as $k) {
            $this->pairs[$k] = $value;
        }
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
     * Insert a pair before a key
     *
     * If the key does not exist, the pair is inserted at the beginning.
     *
     * @param TKey $beforeKey
     * @param iterable<TKey, TValue> $pairs
     */
    function insertBefore(int|string $beforeKey, iterable $pairs): void
    {
        if (\array_key_exists($beforeKey, $this->pairs)) {
            $this->pairs = $this->rebuild(static function ($k, $v) use ($beforeKey, $pairs) {
                if ($k === $beforeKey) {
                    yield from $pairs;
                }

                yield $k => $v;
            })->pairs;
        } else {
            $this->pairs = IterableConverter::toArray($pairs) + $this->pairs;
        }
    }

    /**
     * Insert a pair after a key
     *
     * If the key does not exist, the pair is inserted at the end.
     *
     * @param TKey $afterKey
     * @param iterable<TKey, TValue> $pairs
     */
    function insertAfter(int|string $afterKey, iterable $pairs): void
    {
        if (\array_key_exists($afterKey, $this->pairs)) {
            $this->pairs = $this->rebuild(static function ($k, $v) use ($afterKey, $pairs) {
                yield $k => $v;

                if ($k === $afterKey) {
                    yield from $pairs;
                }
            })->pairs;
        } else {
            $this->add($pairs);
        }
    }

    /**
     * Add pairs from the given iterable to this map
     *
     * If the same key already exists, it will be overwritten.
     *
     * @param iterable<TKey, TValue> $pairs
     */
    function add(iterable $pairs): void
    {
        foreach ($pairs as $k => $v) {
            $this->pairs[$k] = $v;
        }
    }

    /**
     * Remove pairs with the given keys
     *
     * @param TKey ...$keys
     */
    function remove(int|string ...$keys): void
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

    function reduce(callable $reducer, mixed $initial = null): mixed
    {
        $result = $initial;

        foreach ($this->pairs as $key => $value) {
            $result = $reducer($result, $key, $value);
        }

        return $result;
    }

    function slice(int $offset, ?int $length = null): static
    {
        return new static(\array_slice($this->pairs, $offset, $length, true));
    }

    /**
     * @return ObjectList<static<TKey, TValue>>
     */
    function chunk(int $size): ObjectList
    {
        /** @var ObjectList<static<TKey, TValue>> $chunks */
        $chunks = new ObjectList();

        if ($size < 1) {
            return $chunks;
        }

        foreach (\array_chunk($this->pairs, $size, true) as $chunk) {
            /** @psalm-suppress InvalidArgument (https://github.com/vimeo/psalm/issues/10854) */
            $chunks->push(new static($chunk));
        }

        return $chunks;
    }

    /**
     * @return ObjectList<static<TKey, TValue>>
     */
    function split(int $number): ObjectList
    {
        $count = \count($this->pairs);

        if ($count === 0 || $number < 1) {
            /** @var ObjectList<static<TKey, TValue>> */
            return new ObjectList();
        }

        /** @psalm-suppress ArgumentTypeCoercion (size is positive) */
        return $this->chunk((int) \ceil($count / $number));
    }

    function reverse(): static
    {
        return new static(\array_reverse($this->pairs, true));
    }

    function shuffle(?Randomizer $randomizer = null): static
    {
        $randomizer ??= $this->getDefaultRandomizer();

        $pairs = [];

        foreach ($randomizer->shuffleArray(\array_keys($this->pairs)) as $k) {
            $pairs[$k] = $this->pairs[$k];
        }

        return new static($pairs);
    }

    function pick(int $num, ?Randomizer $randomizer = null): static
    {
        if ($num <= 0) {
            return new static();
        }

        if ($num >= \count($this->pairs)) {
            return clone $this;
        }

        $randomizer ??= $this->getDefaultRandomizer();
        $pairs = [];

        foreach ($randomizer->pickArrayKeys($this->pairs, $num) as $k) {
            $pairs[$k] = $this->pairs[$k];
        }

        return new static($pairs);
    }

    function filter(callable $filter): static
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
     * @template TNextValue
     *
     * @param callable(TKey, TValue):TNextValue $callback
     * @return self<TKey, TNextValue>
     */
    function apply(callable $callback): self
    {
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            $pairs[$k] = $callback($k, $v);
        }

        return new self($pairs);
    }

    function walk(callable $callback): void
    {
        foreach ($this->pairs as $k => $v) {
            $callback($k, $v);
        }
    }

    /**
     * @template TOtherKey of array-key
     * @template TOtherValue
     *
     * @param iterable<TOtherKey, TOtherValue> ...$iterables
     * @return self<TKey|TOtherKey, TValue|TOtherValue>
     */
    function merge(iterable ...$iterables): self
    {
        if (\count($iterables) === 0) {
            return clone $this;
        }

        return new static(\array_replace($this->pairs, ...IterableConverter::toArrays($iterables)));
    }

    function intersectUsing(callable $comparator, iterable ...$iterables): static
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toArrays($iterables);
        $args[] = $comparator;

        return new static(\array_uintersect_assoc($this->pairs, ...$args));
    }

    function intersectKeys(iterable ...$iterables): static
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        return new static(\array_intersect_key($this->pairs, ...IterableConverter::toArrays($iterables)));
    }

    function intersectKeysUsing(callable $comparator, iterable ...$iterables): static
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toArrays($iterables);
        $args[] = $comparator;

        return new static(\array_intersect_ukey($this->pairs, ...$args));
    }

    function diffUsing(callable $comparator, iterable ...$iterables): static
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toArrays($iterables);
        $args[] = $comparator;

        return new static(\array_udiff_assoc($this->pairs, ...$args));
    }

    function diffKeys(iterable ...$iterables): static
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        return new static(\array_diff_key($this->pairs, ...IterableConverter::toArrays($iterables)));
    }

    function diffKeysUsing(callable $comparator, iterable ...$iterables): static
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        $args = IterableConverter::toArrays($iterables);
        $args[] = $comparator;


        return new static(\array_diff_ukey($this->pairs, ...$args));
    }

    function sortBy(callable $comparator): static
    {
        if (\count($this->pairs) === 0) {
            return new static();
        }

        $pairs = $this->pairs;
        \uasort($pairs, $comparator);

        return new static($pairs);
    }

    function sortKeys(int $flags = SORT_REGULAR, bool $reverse = false): static
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

    function sortKeysBy(callable $comparator): static
    {
        if (\count($this->pairs) === 0) {
            return new static();
        }

        $pairs = $this->pairs;
        \uksort($pairs, $comparator);

        return new static($pairs);
    }

    /**
     * @template TGroupKey of array-key
     *
     * @param callable(TKey, TValue):TGroupKey $grouper
     * @return ObjectMap<TGroupKey, static<TKey, TValue>>
     */
    function group(callable $grouper): ReadableObjectMap
    {
        /** @var ObjectMap<TGroupKey, static<TKey, TValue>> $groups */
        $groups = new ObjectMap();

        foreach ($this->pairs as $k => $v) {
            /**
             * @psalm-suppress InvalidArgument (https://github.com/vimeo/psalm/issues/10854)
             * @psalm-suppress PossiblyNullReference (https://github.com/vimeo/psalm/issues/10857)
             */
            ($groups[$grouper($k, $v)] ??= new static())->set($k, $v);
        }

        return $groups;
    }

    /**
     * @template TMappedKey of array-key
     *
     * @param callable(TKey, TValue):TMappedKey $mapper
     * @return self<TMappedKey, TValue>
     */
    function remap(callable $mapper): self
    {
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            $pairs[$mapper($k, $v)] = $v;
        }

        return new self($pairs);
    }

    /**
     * @template TMappedKey of array-key
     * @template TMappedValue
     *
     * @param callable(TKey, TValue):iterable<TMappedKey, TMappedValue> $builder
     * @return self<TMappedKey, TMappedValue>
     */
    function rebuild(callable $builder): self
    {
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            foreach ($builder($k, $v) as $mappedKey => $mappedValue) {
                $pairs[$mappedKey] = $mappedValue;
            }
        }

        return new self($pairs);
    }

    function count(): int
    {
        return count($this->pairs);
    }

    /**
     * @param array-key $offset
     */
    function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->pairs);
    }

    /**
     * @param array-key $offset
     * @return TValue|null
     */
    function offsetGet(mixed $offset): mixed
    {
        return $this->pairs[$offset] ?? null;
    }

    /**
     * @note maps do not support appending with "[]" - use {@see Collection}
     *
     * @param TKey $offset
     * @param TValue $value
     */
    function offsetSet(mixed $offset, mixed $value): void
    {
        $this->pairs[$offset] = $value;
    }

    /**
     * @param TKey $offset
     */
    function offsetUnset(mixed $offset): void
    {
        unset($this->pairs[$offset]);
    }

    function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->pairs);
    }

    protected function getDefaultRandomizer(): Randomizer
    {
        /** @var Randomizer $randomizer */
        static $randomizer = new Randomizer();

        return $randomizer;
    }
}
