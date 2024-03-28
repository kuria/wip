<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\Iterable\IterableConverter;

/**
 * Map of scalar values
 *
 * @template TKey of array-key
 * @template TValue of scalar
 * @extends Map<TKey, TValue>
 * @implements ReadableScalarMap<TKey, TValue>
 */
class ScalarMap extends Map implements ReadableScalarMap
{
    /**
     * @return ScalarList<TValue>
     */
    function values(): ScalarList
    {
        return new ScalarList(\array_values($this->pairs));
    }

    /**
     * @return self<array-key, TKey>
     */
    function flip(): self
    {
        /** @var array<array-key, TKey> $pairs */
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            $pairs[(string) $v] = $k;
        }

        return new self($pairs);
    }

    function intersect(iterable ...$iterables): static
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        return new static(\array_intersect_assoc($this->pairs, ...IterableConverter::toArrays($iterables)));
    }

    function diff(iterable ...$iterables): static
    {
        if (\count($this->pairs) === 0 || \count($iterables) === 0) {
            return new static();
        }

        return new static(\array_diff_assoc($this->pairs, ...IterableConverter::toArrays($iterables)));
    }

    function sort(int $flags = SORT_REGULAR, bool $reverse = false): static
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
     * @template TMappedKey of array-key
     *
     * @param callable(TKey, TValue):TMappedKey $mapper
     * @return self<TMappedKey, TValue>
     */
    function remap(callable $mapper): self
    {
        return parent::remap($mapper)->asScalars();
    }
}
