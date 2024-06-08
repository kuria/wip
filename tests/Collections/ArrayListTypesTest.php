<?php declare(strict_types=1);

namespace Kuria\Collections;

use function Kuria\Tools\testExactPHPStanType;
use function Kuria\Tools\testExactType;
use function Kuria\Tools\testPsalmType;
use function Kuria\Tools\testType;

class ArrayListTypesTest
{
    /**
     * @param iterable<array{foo: int, bar: string}> $iterable
     * @param array<int, string> $array
     */
    function testFactories(iterable $iterable, array $array): void
    {
        testPsalmType('Kuria\Collections\ArrayList<array{foo: int, bar: string}>', ArrayList::fromIterable($iterable));
        testPsalmType('Kuria\Collections\ArrayList<array<int, string>>', ArrayList::collect($array, $array));

        // PHPStan doesn't support @return static<TValue>
        testExactPHPStanType(ArrayList::class, ArrayList::fromIterable($iterable));
        testExactPHPStanType(ArrayList::class, ArrayList::collect($array, $array));
    }

    /**
     * @param ArrayList<array{foo: int, bar: string}> $arrays
     */
    function testReadApi(Collection $arrays): void
    {
        testType('list<array{foo: int, bar: string}>', $arrays->toArray());
        testType('bool', $arrays->isEmpty());
        testType('bool', $arrays->has(5));
        testType('bool', $arrays->contains(['foo' => 123, 'bar' => 'test']));
        testType('bool', $arrays->contains(123)); // should not fail for other types
        testType('Kuria\Maybe\Maybe<non-negative-int>', $arrays->find(['foo' => 123, 'bar' => 'test']));
        testType('Kuria\Maybe\Maybe<non-negative-int>', $arrays->find(123)); // should not fail for other types
        testType('Kuria\Maybe\Maybe<array{foo: int, bar: string}>', $arrays->findUsing(fn (array $v) => $v['bar'] === 'test'));
        testType('Kuria\Maybe\Maybe<non-negative-int>', $arrays->findIndexUsing(fn (array $v) => $v['bar'] === 'test'));
        testType('bool', $arrays->any(fn (array $v) => $v['bar'] === 'test'));
        testType('bool', $arrays->all(fn (array $v) => $v['bar'] === 'test'));
        testType('Kuria\Maybe\Maybe<array{foo: int, bar: string}>', $arrays->get(5));
        testType('Kuria\Maybe\Maybe<array{foo: int, bar: string}>', $arrays->first());
        testType('Kuria\Maybe\Maybe<array{foo: int, bar: string}>', $arrays->last());
        testType('Kuria\Maybe\Maybe<0>', $arrays->firstIndex());
        testType('Kuria\Maybe\Maybe<non-negative-int>', $arrays->lastIndex());
        testType('Kuria\Maybe\Maybe<array{foo: int, bar: string}>', $arrays->random());
        testType('Kuria\Maybe\Maybe<non-negative-int>', $arrays->randomIndex());
    }

    /**
     * @param ArrayList<array{name: string, value: int}> $arrays
     */
    function testWriteApi(ArrayList $arrays): void
    {
        $arrays->set(0, ['name' => 'a', 'value' => 0]);
        $arrays->setValues([
            ['name' => 'a', 'value' => 0],
            ['name' => 'b', 'value' => 1],
            ['name' => 'c', 'value' => 2],
        ]);
        $arrays->remove(0, 2);
        $arrays->clear();
        $arrays->push(['name' => 'a', 'value' => 0], ['name' => 'b', 'value' => 1]);
        testType('Kuria\Maybe\Maybe<array{name: string, value: int}>', $arrays->pop());
        testType('Kuria\Maybe\Maybe<array{name: string, value: int}>', $arrays->shift());
        $arrays->unshift(['name' => 'foo', 'value' => 0]);
        $arrays->add([['name' => 'baz', 'value' => 2], ['name' => 'qux', 'value' => 3]]);
        $arrays->insert(1, ['name' => 'bar', 'value' => 1]);
        $arrays->splice(2, 2);
        $arrays->pad(4, ['name' => 'x', 'value' => 99]);
    }

    /**
     * @param ArrayList<array{name: string, value: int}> $arrays
     * @param ArrayList<array{name: string, value: int}> $otherArrays
     */
    function testTransformations(
        ArrayList $arrays,
        ArrayList $otherArrays,
        string $string,
        int $int,
        bool $bool,
    ): void {
        testType('string|null', $arrays->reduce(fn (?string $result, array $v) => ($result ?? '') . $v['name'] . "\n"));
        testType('Kuria\Collections\ArrayList<array{name: string, value: int}>&static', $arrays->slice(0, 3));
        testPsalmType('Kuria\Collections\ObjectList<Kuria\Collections\ArrayList<array{name: string, value: int}>&static>', $arrays->chunk(10));
        testPsalmType('Kuria\Collections\ObjectList<Kuria\Collections\ArrayList<array{name: string, value: int}>&static>', $arrays->split(2));
        testType('Kuria\Collections\ArrayList<array{name: string, value: int}>&static', $arrays->reverse());
        testType('Kuria\Collections\ArrayList<array{name: string, value: int}>&static', $arrays->shuffle());
        testType('Kuria\Collections\ArrayList<array{name: string, value: int}>&static', $arrays->pick(3));
        testType('Kuria\Collections\ArrayList<array{name: string, value: int}>&static', $arrays->filter(fn (array $v) => $v['name'] !== 'foo'));
        testType('Kuria\Collections\Collection<non-negative-int>', $arrays->apply(fn (array $v) => strlen($v['name'])));
        $arrays->walk(fn (array $v) => \var_dump($v));
        testType('Kuria\Collections\Collection<array{name: string, value: int}|int|bool>', $arrays->merge([$int, $int], [$bool]));
        testType('Kuria\Collections\ArrayList<array{name: string, value: int}>&static', $arrays->intersectUsing(fn (array $a, array $b) => \strcmp($a['name'], $b['name']), $otherArrays));
        testType('Kuria\Collections\ArrayList<array{name: string, value: int}>&static', $arrays->diffUsing(fn (array $a, array $b) => \strcmp($a['name'], $b['name']), $otherArrays));
        testType('Kuria\Collections\ArrayList<array{name: string, value: int}>&static', $arrays->sortBy(fn (array $a, array $b) => \strcmp($a['name'], $b['name'])));
        testPsalmType('Kuria\Collections\ObjectMap<int, Kuria\Collections\ArrayList<array{name: string, value: int}>&static>', $arrays->group(fn (int $i, array $v) => $int));
        testPsalmType('Kuria\Collections\ObjectMap<string, Kuria\Collections\ArrayList<array{name: string, value: int}>&static>', $arrays->group(fn (int $i, array $v) => $string));
        testType('Kuria\Collections\ArrayMap<string, array{name: string, value: int}>', $arrays->map(fn (int $i, array $v) => $string));
        testType('Kuria\Collections\ArrayMap<int, array{name: string, value: int}>', $arrays->map(fn (int $i, array $v) => $int));
        testType('Kuria\Collections\Map<string, int>', $arrays->buildMap(fn (int $i, array $v) => yield $string => $int));
        testType('Kuria\Collections\Map<int, string>', $arrays->buildMap(fn (int $i, array $v) => yield $int => $string));
        testType('Kuria\Collections\ArrayMap<non-negative-int, array{name: string, value: int}>', $arrays->toMap());
        // TODO: phpstan doesnt seem to understand those
        testType('Kuria\Collections\Collection<string>', $arrays->column('name'));
        testType('Kuria\Collections\ArrayMap<int, array{name: string, value: int}>', $arrays->mapColumn('value'));
        testExactType('Kuria\Collections\Map<int, string>', $arrays->mapColumn('value', 'name'));

        // PHPStan doesn't support @return containing static<T>
        testExactPHPStanType('Kuria\Collections\ObjectList<Kuria\Collections\Collection<array{name: string, value: int}>>', $arrays->chunk(10));
        testExactPHPStanType('Kuria\Collections\ObjectList<Kuria\Collections\Collection<array{name: string, value: int}>>', $arrays->split(2));
        testExactPHPStanType('Kuria\Collections\ObjectMap<int, Kuria\Collections\Collection<array{name: string, value: int}>>', $arrays->group(fn (int $i, array $v) => $int));
        testExactPHPStanType('Kuria\Collections\ObjectMap<string, Kuria\Collections\Collection<array{name: string, value: int}>>', $arrays->group(fn (int $i, array $v) => $string));

    }

    /**
     * @param ArrayList<array{name: string, value: int}> $arrays
     */
    function testTypeCast(ArrayList $arrays): void
    {
        testType('Kuria\Collections\Collection', $arrays->as(Collection::class));
    }

    /**
     * @param ArrayList<array{name: string, value: int}> $arrays
     */
    function testPhpInterfaces(ArrayList $arrays): void
    {
        // ArrayAccess
        testType('bool', isset($arrays[0]));
        testType('array{name: string, value: int}|null', $arrays[0]);
        $arrays[] = ['name' => 'bar', 'value' => 1];
        $arrays[2] = ['name' => 'baz', 'value' => 2];
        unset($arrays[1]);
        testType('Kuria\Collections\ArrayList<array{name: string, value: int}>', $arrays);

        // Countable
        testType('non-negative-int', $arrays->count());

        // Traversable
        testType('Traversable<non-negative-int, array{name: string, value: int}>', $arrays->getIterator());
    }
}
