<?php declare(strict_types=1);

namespace Kuria\Collections;

use function Kuria\Tools\testExactPHPStanType;
use function Kuria\Tools\testPsalmType;
use function Kuria\Tools\testType;

class MapTypesTest
{
    /**
     * @param iterable<int, string> $iterable
     */
    function testFactories(iterable $iterable, int $int): void
    {
        testPsalmType('Kuria\Collections\Map<int, string>', Map::fromIterable($iterable));
        testPsalmType('Kuria\Collections\Map<string, int>', Map::combine($iterable, [$int, $int]));

        // PHPStan doesn't support @return static<TValue> (https://github.com/phpstan/phpstan/issues/5512)
        testExactPHPStanType(Map::class, Map::fromIterable($iterable));
        testExactPHPStanType(Map::class, Map::combine($iterable, [$int, $int]));
    }

    /**
     * @param Map<string, int> $map
     */
    function testReadApi(Map $map): void
    {
        testType('array<string, int>', $map->toArray());
        testType('bool', $map->isEmpty());
        testType('bool', $map->has('foo'));
        testType('bool', $map->has(123)); // should not fail for other types
        testType('bool', $map->contains('foo'));
        testType('bool', $map->contains(123)); // should not fail for other types
        testType('Kuria\Maybe\Maybe<string>', $map->find('foo'));
        testType('Kuria\Maybe\Maybe<string>', $map->find(123)); // should not fail for other types
        testType('Kuria\Maybe\Maybe<string>', $map->findUsing(fn (int $v) => $v === 123));
        testType('Kuria\Maybe\Maybe<int>', $map->get(5));
        testType('Kuria\Maybe\Maybe<int>', $map->first());
        testType('Kuria\Maybe\Maybe<int>', $map->last());
        testType('Kuria\Maybe\Maybe<string>', $map->firstKey());
        testType('Kuria\Maybe\Maybe<string>', $map->lastKey());
        testType('Kuria\Maybe\Maybe<int>', $map->random());
        testType('Kuria\Maybe\Maybe<string>', $map->randomKey());
        testType('Kuria\Collections\ScalarList<string>', $map->keys());
        testType('Kuria\Collections\Collection<int>', $map->values());
    }

    /**
     * @param Map<string, int> $map
     */
    function testWriteApi(Map $map): void
    {
        $map->set('foo', 0);
        $map->setMultiple(['foo', 'bar', 'baz'], 123);
        $map->setPairs(['bar' => 2, 'baz' => 3]);
        $map->insertBefore('bar', ['foo' => 1]);
        $map->insertAfter('baz', ['qux' => 4]);
        $map->add(['foo' => 1, 'bar' => 2]);
        $map->remove('foo', 'bar');
        $map->clear();
    }

    /**
     * @param Map<string, int> $map
     * @param Map<string, int> $otherMap
     */
    function testTransformations(
        Map $map,
        Map $otherMap,
        string $string,
        int $int,
    ): void {
        testType('string|null', $map->reduce(fn (?string $result, string $k, int $v) => ($result ?? '') . "{$k}={$v}\n"));
        testType('Kuria\Collections\Map<string, int>&static', $map->slice(0, 3));
        testType('Kuria\Collections\ObjectList<Kuria\Collections\Map<string, int>&static>', $map->chunk(10));
        testType('Kuria\Collections\ObjectList<Kuria\Collections\Map<string, int>&static>', $map->split(2));
        testType('Kuria\Collections\Map<string, int>&static', $map->reverse());
        testType('Kuria\Collections\Map<string, int>&static', $map->shuffle());
        testType('Kuria\Collections\Map<string, int>&static', $map->pick(3));
        testType('Kuria\Collections\Map<string, int>&static', $map->filter(fn (string $k, int $v) => $k !== 'foo'));
        testType('Kuria\Collections\Map<string, float>', $map->apply(fn (string $k, int $v) => (float) $v));
        $map->walk(fn (string $k, int $v) => \var_dump($k, $v));
        testPsalmType('Kuria\Collections\Map<string|int, int|bool>', $map->merge(['foo' => 123], [123 => false]));
        testExactPHPStanType('Kuria\Collections\Map<string, bool|int>', $map->merge(['foo' => 123], [123 => false])); // https://github.com/phpstan/phpstan/issues/10871
        testType('Kuria\Collections\Map<string, int>&static', $map->intersectUsing(fn (int $a, int $b) => $a <=> $b, $otherMap));
        testType('Kuria\Collections\Map<string, int>&static', $map->intersectKeys([123 => 456], $otherMap));
        testType('Kuria\Collections\Map<string, int>&static', $map->intersectKeysUsing(fn (int|string $a, int|string $b) => $a <=> $b, [123 => 456], $otherMap));
        testType('Kuria\Collections\Map<string, int>&static', $map->diffUsing(fn (int $a, int $b) => $a <=> $b, $otherMap));
        testType('Kuria\Collections\Map<string, int>&static', $map->diffKeys([123 => 456], $otherMap));
        testType('Kuria\Collections\Map<string, int>&static', $map->diffKeysUsing(fn (int|string $a, int|string $b) => $a <=> $b, [123 => 456], $otherMap));
        testType('Kuria\Collections\Map<string, int>&static', $map->sortBy(fn (int $a, int $b) => $a <=> $b));
        testType('Kuria\Collections\Map<string, int>&static', $map->sortKeys());
        testType('Kuria\Collections\Map<string, int>&static', $map->sortKeysBy(\strcmp(...)));
        testType('Kuria\Collections\ObjectMap<int, Kuria\Collections\Map<string, int>&static>', $map->group(fn (string $k, int $v) => $int));
        testType('Kuria\Collections\ObjectMap<string, Kuria\Collections\Map<string, int>&static>', $map->group(fn (string $k, int $v) => $string));
        testType('Kuria\Collections\Map<string, int>', $map->remap(fn (string $k, int $v) => $string));
        testType('Kuria\Collections\Map<int, int>', $map->remap(fn (string $k, int $v) => $int));
        testType('Kuria\Collections\Map<string, int>', $map->rebuild(fn (string $k, int $v) => yield $string => $int));
        testType('Kuria\Collections\Map<int, string>', $map->rebuild(fn (string $k, int $v) => yield $int => $string));
    }

    /**
     * @param Map<string, mixed> $mixed
     * @param Map<string, int> $ints
     * @param Map<string, \stdClass> $objects
     * @param Map<string, array{key: int}> $arrays
     */
    function testTypeCast(
        Map $mixed,
        Map $ints,
        Map $objects,
        Map $arrays,
    ): void
    {
        // custom type as class-string
        testType('Kuria\Collections\ScalarMap<array-key, scalar>', $mixed->as(ScalarMap::class));

        // @TODO should be ScalarMap<string, int> when tools start supporting it
        // - https://github.com/vimeo/psalm/issues/7913
        // - https://github.com/phpstan/phpstan/issues/4971
        testType('Kuria\Collections\ScalarMap<array-key, scalar>', $ints->as(ScalarMap::class));

        // built-in type casts
        // @TODO potentially unsafe
        // - https://github.com/vimeo/psalm/discussions/10864 (@psalm-if-this-is doesn't play well with invariant collections)
        // - https://github.com/phpstan/phpstan/discussions/10285 (PHPStan doesn't support this kind of assertion yet)
        testType('Kuria\Collections\ScalarMap<string, mixed>', $mixed->asScalars());
        testType('Kuria\Collections\ScalarMap<string, int>', $ints->asScalars());
        testType('Kuria\Collections\ObjectMap<string, mixed>', $mixed->asObjects());
        testType('Kuria\Collections\ObjectMap<string, stdClass>', $objects->asObjects());
        testType('Kuria\Collections\ArrayMap<string, mixed>', $mixed->asArrays());
        testType('Kuria\Collections\ArrayMap<string, array{key: int}>', $arrays->asArrays());
    }

    /**
     * @param Map<string, int> $map
     */
    function testPhpInterfaces(Map $map): void
    {
        // ArrayAccess
        testType('bool', isset($map['foo']));
        testType('int|null', $map['foo']);
        $map['foo'] = 123;
        unset($map['foo']);
        testType('Kuria\Collections\Map<string, int>', $map);

        // Countable
        testType('non-negative-int', $map->count());

        // Traversable
        testType('Traversable<string, int>', $map->getIterator());
    }
}
