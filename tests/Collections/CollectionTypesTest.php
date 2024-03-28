<?php declare(strict_types=1);

namespace Kuria\Collections;

use function Kuria\Psalm\assertType;

class CollectionTypesTest
{
    /**
     * @param iterable<string> $stringIterable
     */
    function testFactories(iterable $stringIterable, int $int): void
    {
        assertType('Kuria\Collections\Collection<string>', Collection::fromIterable($stringIterable));
        assertType('Kuria\Collections\Collection<int>', Collection::collect($int, $int));
    }

    /**
     * @param Collection<string> $strings
     */
    function testReadApi(Collection $strings): void
    {
        assertType('list<string>', $strings->toArray());
        assertType('bool', $strings->isEmpty());
        assertType('bool', $strings->has(5));
        assertType('bool', $strings->contains('foo'));
        assertType('bool', $strings->contains(123)); // should not fail for other types
        assertType('Kuria\Maybe\Maybe<non-negative-int>', $strings->find('foo'));
        assertType('Kuria\Maybe\Maybe<non-negative-int>', $strings->find(123)); // should not fail for other types
        assertType('Kuria\Maybe\Maybe<non-negative-int>', $strings->findUsing(fn (string $s) => $s === 'foo'));
        assertType('Kuria\Maybe\Maybe<string>', $strings->get(5));
        assertType('Kuria\Maybe\Maybe<string>', $strings->first());
        assertType('Kuria\Maybe\Maybe<string>', $strings->last());
    }

    /**
     * @param Collection<string> $strings
     */
    function testWriteApi(Collection $strings): void
    {
        $strings->set(0, 'foo');
        $strings->setValues(['foo', 'bar', 'baz']);
        $strings->remove(0, 2);
        $strings->clear();
        $strings->push('lorem', 'ipsum');
        assertType('Kuria\Maybe\Maybe<string>', $strings->pop());
        assertType('Kuria\Maybe\Maybe<string>', $strings->shift());
        $strings->unshift('foo');
        $strings->add(['baz', 'qux']);
        $strings->insert(1, 'bar');
        $strings->splice(2, 2);
        $strings->pad(4, 'x');
    }

    /**
     * @param Collection<string> $strings
     * @param Collection<string> $otherStrings
     * @param callable(string):int $action
     */
    function testTransformations(
        Collection $strings,
        Collection $otherStrings,
        callable $action,
        int $int,
        bool $bool,
    ): void {
        assertType('string|null', $strings->reduce(fn (?string $result, string $v) => ($result ?? '') . $v . "\n"));
        assertType('Kuria\Collections\Collection<string>&static', $strings->slice(0, 3));
        assertType('Kuria\Collections\ObjectList<Kuria\Collections\Collection<string>&static>', $strings->chunk(10));
        assertType('Kuria\Collections\ObjectList<Kuria\Collections\Collection<string>&static>', $strings->split(2));
        assertType('Kuria\Collections\Collection<string>&static', $strings->reverse());
        assertType('Kuria\Collections\Collection<string>&static', $strings->shuffle());
        assertType('Kuria\Collections\Collection<string>&static', $strings->random(3));
        assertType('Kuria\Collections\Collection<string>&static', $strings->filter(fn (string $v) => $v !== 'foo'));
        assertType('Kuria\Collections\Collection<non-negative-int>', $strings->apply(strlen(...)));
        $strings->walk(fn (string $v) => $action($v));
        assertType('Kuria\Collections\Collection<string|int|bool>', $strings->merge([$int, $int], [$bool]));
        assertType('Kuria\Collections\Collection<string>&static', $strings->intersectUsing(\strcmp(...), $otherStrings));
        assertType('Kuria\Collections\Collection<string>&static', $strings->diffUsing(\strcmp(...), $otherStrings));
        assertType('Kuria\Collections\Collection<string>&static', $strings->sortBy(\strcmp(...)));
        assertType('Kuria\Collections\ObjectMap<int<0, 1>, Kuria\Collections\Collection<string>&static>', $strings->group(fn (int $i, string $v) => $i % 2));
        assertType('Kuria\Collections\ObjectMap<string, Kuria\Collections\Collection<string>&static>', $strings->group(fn (int $i, string $v) => $v[0] ?? ''));
        assertType('Kuria\Collections\Map<non-falsy-string, string>', $strings->map(fn (int $i, string $v) => \md5($v)));
        assertType('Kuria\Collections\Map<non-negative-int, string>', $strings->map(fn (int $i, string $v) => $i * 10));
        assertType('Kuria\Collections\Map<non-negative-int, string>', $strings->buildMap(fn (int $i, string $v) => yield $i => $v));
        assertType('Kuria\Collections\Map<non-falsy-string, non-negative-int>', $strings->buildMap(fn (int $i, string $v) => yield \md5($v) => \strlen($v)));
        assertType('Kuria\Collections\Map<non-negative-int, string>', $strings->toMap());
    }

    /**
     * @param Collection<mixed> $mixed
     * @param Collection<int> $ints
     * @param Collection<\stdClass> $objects
     * @param Collection<array{key: int}> $arrays
     */
    function testTypeCast(
        Collection $mixed,
        Collection $ints,
        Collection $objects,
        Collection $arrays,
    ): void
    {
        // custom type as class-string
        assertType('Kuria\Collections\ScalarList<scalar>', $mixed->as(ScalarList::class));
        // @TODO should be ScalarList<int> if https://github.com/vimeo/psalm/issues/7913 is resolved
        assertType('Kuria\Collections\ScalarList<scalar>', $ints->as(ScalarList::class));

        // built-in type casts
        // potentially unsafe, can't be verified by @psalm-if-this-is currently - https://github.com/vimeo/psalm/discussions/10864
        assertType('Kuria\Collections\ScalarList<mixed>', $mixed->asScalars());
        assertType('Kuria\Collections\ScalarList<int>', $ints->asScalars());
        assertType('Kuria\Collections\ObjectList<mixed>', $mixed->asObjects());
        assertType('Kuria\Collections\ObjectList<stdClass>', $objects->asObjects());
        assertType('Kuria\Collections\ArrayList<mixed>', $mixed->asArrays());
        assertType('Kuria\Collections\ArrayList<array{key: int}>', $arrays->asArrays());
    }

    /**
     * @param Collection<string> $strings
     */
    function testPhpInterfaces(Collection $strings): void
    {
        // ArrayAccess
        assertType('bool', isset($strings[0]));
        assertType('string|null', $strings[0]);
        $strings[] = 'bar';
        $strings[2] = 'baz';
        unset($strings[1]);
        assertType('Kuria\Collections\Collection<string>', $strings);

        // Countable
        assertType('non-negative-int', $strings->count());

        // Traversable
        assertType('Traversable<non-negative-int, string>', $strings->getIterator());
    }
}
