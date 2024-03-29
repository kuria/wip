<?php declare(strict_types=1);

namespace Kuria\Collections;

use function Kuria\Tools\testExactPHPStanType;
use function Kuria\Tools\testPsalmType;
use function Kuria\Tools\testType;

class CollectionTypesTest
{
    /**
     * @param iterable<string> $stringIterable
     */
    function testFactories(iterable $stringIterable, int $int): void
    {
        testPsalmType('Kuria\Collections\Collection<string>', Collection::fromIterable($stringIterable));
        testPsalmType('Kuria\Collections\Collection<int>', Collection::collect($int, $int));

        // PHPStan doesn't support @return static<TValue>
        testExactPHPStanType(Collection::class, Collection::fromIterable($stringIterable));
        testExactPHPStanType(Collection::class, Collection::collect($int, $int));
    }

    /**
     * @param Collection<string> $strings
     */
    function testReadApi(Collection $strings): void
    {
        testType('list<string>', $strings->toArray());
        testType('bool', $strings->isEmpty());
        testType('bool', $strings->has(5));
        testType('bool', $strings->contains('foo'));
        testType('bool', $strings->contains(123)); // should not fail for other types
        testType('Kuria\Maybe\Maybe<non-negative-int>', $strings->find('foo'));
        testType('Kuria\Maybe\Maybe<non-negative-int>', $strings->find(123)); // should not fail for other types
        testType('Kuria\Maybe\Maybe<non-negative-int>', $strings->findUsing(fn (string $s) => $s === 'foo'));
        testType('Kuria\Maybe\Maybe<string>', $strings->get(5));
        testType('Kuria\Maybe\Maybe<string>', $strings->first());
        testType('Kuria\Maybe\Maybe<string>', $strings->last());
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
        testType('Kuria\Maybe\Maybe<string>', $strings->pop());
        testType('Kuria\Maybe\Maybe<string>', $strings->shift());
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
        string $string,
        int $int,
        bool $bool,
    ): void {
        testType('string|null', $strings->reduce(fn (?string $result, string $v) => ($result ?? '') . $v . "\n"));
        testType('Kuria\Collections\Collection<string>&static', $strings->slice(0, 3));
        testType('Kuria\Collections\ObjectList<Kuria\Collections\Collection<string>&static>', $strings->chunk(10));
        testType('Kuria\Collections\ObjectList<Kuria\Collections\Collection<string>&static>', $strings->split(2));
        testType('Kuria\Collections\Collection<string>&static', $strings->reverse());
        testType('Kuria\Collections\Collection<string>&static', $strings->shuffle());
        testType('Kuria\Collections\Collection<string>&static', $strings->random(3));
        testType('Kuria\Collections\Collection<string>&static', $strings->filter(fn (string $v) => $v !== 'foo'));
        testType('Kuria\Collections\Collection<non-negative-int>', $strings->apply(strlen(...)));
        $strings->walk(fn (string $v) => $action($v));
        testType('Kuria\Collections\Collection<string|int|bool>', $strings->merge([$int, $int], [$bool]));
        testType('Kuria\Collections\Collection<string>&static', $strings->intersectUsing(\strcmp(...), $otherStrings));
        testType('Kuria\Collections\Collection<string>&static', $strings->diffUsing(\strcmp(...), $otherStrings));
        testType('Kuria\Collections\Collection<string>&static', $strings->sortBy(\strcmp(...)));
        testType('Kuria\Collections\ObjectMap<int, Kuria\Collections\Collection<string>&static>', $strings->group(fn (int $i, string $v) => $int));
        testType('Kuria\Collections\ObjectMap<string, Kuria\Collections\Collection<string>&static>', $strings->group(fn (int $i, string $v) => $string));
        testType('Kuria\Collections\Map<string, string>', $strings->map(fn (int $i, string $v) => $string));
        testType('Kuria\Collections\Map<int, string>', $strings->map(fn (int $i, string $v) => $int));
        testType('Kuria\Collections\Map<string, int>', $strings->buildMap(fn (int $i, string $v) => yield $string => $int));
        testType('Kuria\Collections\Map<int, string>', $strings->buildMap(fn (int $i, string $v) => yield $int => $string));
        testType('Kuria\Collections\Map<non-negative-int, string>', $strings->toMap());
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
        testType('Kuria\Collections\ScalarList<scalar>', $mixed->as(ScalarList::class));

        // @TODO should be ScalarList<int> when tools start supporting it
        // - https://github.com/vimeo/psalm/issues/7913
        // - https://github.com/phpstan/phpstan/issues/4971
        testType('Kuria\Collections\ScalarList<scalar>', $ints->as(ScalarList::class));

        // built-in type casts
        // @TODO potentially unsafe
        // - https://github.com/vimeo/psalm/discussions/10864 (@psalm-if-this-is doesn't play well with invariant collections)
        // - https://github.com/phpstan/phpstan/discussions/10285 (PHPStan doesn't support this kind of assertion yet)
        testType('Kuria\Collections\ScalarList<mixed>', $mixed->asScalars());
        testType('Kuria\Collections\ScalarList<int>', $ints->asScalars());
        testType('Kuria\Collections\ObjectList<mixed>', $mixed->asObjects());
        testType('Kuria\Collections\ObjectList<stdClass>', $objects->asObjects());
        testType('Kuria\Collections\ArrayList<mixed>', $mixed->asArrays());
        testType('Kuria\Collections\ArrayList<array{key: int}>', $arrays->asArrays());
    }

    /**
     * @param Collection<string> $strings
     */
    function testPhpInterfaces(Collection $strings): void
    {
        // ArrayAccess
        testType('bool', isset($strings[0]));
        testType('string|null', $strings[0]);
        $strings[] = 'bar';
        $strings[2] = 'baz';
        unset($strings[1]);
        testType('Kuria\Collections\Collection<string>', $strings);

        // Countable
        testType('non-negative-int', $strings->count());

        // Traversable
        testType('Traversable<non-negative-int, string>', $strings->getIterator());
    }
}
