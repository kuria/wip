<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * Sequential list of scalar values
 *
 * @template T of object
 * @extends Collection<T>
 * @implements ReadableObjectList<T>
 */
class ObjectList extends Collection implements ReadableObjectList
{
    function unique(): static
    {
        $seen = [];
        $unique = [];

        foreach ($this->values as $object) {
            $id = \spl_object_id($object);

            if (isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $unique[] = $object;
        }

        return new static($unique);
    }

    function intersect(iterable ...$iterables): static
    {
        return $this->intersectUsing($this->compareObjects(...), ...$iterables);
    }

    function diff(iterable ...$iterables): static
    {
        return $this->diffUsing($this->compareObjects(...), ...$iterables);
    }

    /**
     * @template TMappedKey of array-key
     *
     * @param callable(non-negative-int, T):TMappedKey $mapper
     * @return ObjectMap<TMappedKey, T>
     */
    function map(callable $mapper): ObjectMap
    {
        return parent::map($mapper)->asObjects();
    }

    /**
     * @return ObjectMap<non-negative-int, T>
     */
    function toMap(): ObjectMap
    {
        return new ObjectMap($this->values);
    }

    /**
     * @template TProp of string
     * @psalm-suppress all
     *
     * @param TProp $prop
     * @return Collection<T[TProp]>
     */
    function column(string $prop): Collection
    {
        return new Collection(\array_column($this->values, $prop));
    }

    /**
     * @template TProp of string
     * @template TValueProp of string
     * @psalm-suppress all
     *
     * @param TProp $prop
     * @param TValueProp|null $valueProp
     *
     * @return ($valueProp is null ? ObjectMap<T[TProp], T> : Map<T[TProp], T[TValueProp]>)
     */
    function mapColumn(string $prop, ?string $valueProp = null): Map
    {
        if ($valueProp !== null) {
            return new Map(\array_column($this->values, $valueProp, $prop));
        }

        return new ObjectMap(\array_column($this->values, null, $prop));
    }

    protected function compareObjects(object $a, object $b): int
    {
        return \spl_object_id($a) <=> \spl_object_id($b);
    }
}
