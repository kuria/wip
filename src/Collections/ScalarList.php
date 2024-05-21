<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\Iterable\IterableConverter;

/**
 * Sequential list of scalar values
 *
 * @template T of scalar
 * @extends Collection<T>
 * @implements ReadableScalarList<T>
 */
class ScalarList extends Collection implements ReadableScalarList
{
    /**
     * Create a scalar list by splitting a string
     *
     * If $limit is negative, all parts except the last -$limit will be returned.
     *
     * @param non-empty-string $delimiter
     * @return self<string>
     */
    static function explode(string $string, string $delimiter, int $limit = PHP_INT_MAX): self
    {
        return new self(\explode($delimiter, $string, $limit));
    }

    function sum(): int|float
    {
        return \array_sum($this->values);
    }

    function product(): int|float
    {
        return \array_product($this->values);
    }

    function implode(string $delimiter = ''): string
    {
        return \implode($delimiter, $this->values);
    }

    function unique(): static
    {
        return new static(\array_values(\array_unique($this->values, \SORT_REGULAR)));
    }

    function intersect(iterable ...$iterables): static
    {
        if (\count($this->values) === 0 || \count($iterables) === 0) {
            /** @var static<T> */
            return new static();
        }

        return new static(\array_values(\array_intersect($this->values, ...IterableConverter::toLists($iterables))));
    }

    function diff(iterable ...$iterables): static
    {
        if (\count($this->values) === 0 || \count($iterables) === 0) {
            /** @var static<T> */
            return new static();
        }

        return new static(\array_values(\array_diff($this->values, ...IterableConverter::toLists($iterables))));
    }

    function sort(int $flags = \SORT_REGULAR, bool $reverse = false): static
    {
        if (\count($this->values) === 0) {
            /** @var static<T> */
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
     * @template TMappedKey of array-key
     *
     * @param callable(non-negative-int, T):TMappedKey $mapper
     * @return ScalarMap<TMappedKey, T>
     */
    function map(callable $mapper): ScalarMap
    {
        return parent::map($mapper)->asScalars();
    }

    /**
     * @return ScalarMap<non-negative-int, T>
     */
    function toMap(): ScalarMap
    {
        /** @var ScalarMap<non-negative-int, T> */
        return new ScalarMap($this->values);
    }
}
