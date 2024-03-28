<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @template TKey of array-key
 * @template TValue of object
 * @extends Map<TKey, TValue>
 * @implements ReadableObjectMap<TKey, TValue>
 */
class ObjectMap extends Map implements ReadableObjectMap
{
    /**
     * @return ObjectList<TValue>
     */
    function values(): ObjectList
    {
        return new ObjectList(\array_values($this->pairs));
    }

    function intersect(iterable ...$iterables): static
    {
        return $this->intersectUsing($this->compareObjects(...));
    }

    function diff(iterable ...$iterables): static
    {
        return $this->diffUsing($this->compareObjects(...));
    }

    /**
     * @template TMappedKey of array-key
     *
     * @param callable(TKey, TValue):TMappedKey $mapper
     * @return self<TMappedKey, TValue>
     */
    function remap(callable $mapper): self
    {
        return parent::remap($mapper)->asObjects();
    }

    /**
     * @return ($indexBy is null ? Map<TKey, mixed> : Map<array-key, mixed>)
     */
    function column(string $prop, int|string|null $indexBy = null): Map
    {
        if ($indexBy !== null) {
            return new Map(\array_column($this->pairs, $prop, $indexBy));
        }

        // cannot use \array_column() here because it does not preserve keys
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            if (
                isset($v->{$prop})
                || \property_exists($v, $prop) && (new \ReflectionProperty($v, $prop))->isPublic()
            ) {
                /** @psalm-suppress MixedAssignment */
                $pairs[$k] = $v->{$prop};
            }
        }

        return new Map($pairs);
    }

    /**
     * @return self<array-key, TValue>
     */
    function indexBy(string $prop): self
    {
        return new self(\array_column($this->pairs, null, $prop));
    }

    protected function compareObjects(object $a, object $b): int
    {
        return \spl_object_id($a) <=> \spl_object_id($b);
    }
}
