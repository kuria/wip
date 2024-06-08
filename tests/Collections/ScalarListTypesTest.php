<?php declare(strict_types=1);

namespace Kuria\Collections;

use function Kuria\Tools\testExactPHPStanType;
use function Kuria\Tools\testPsalmType;
use function Kuria\Tools\testType;

class ScalarListTypesTest
{
    /**
     * @param iterable<string> $iterable
     */
    function testFactories(iterable $iterable, int $int): void
    {
        testPsalmType('Kuria\Collections\ScalarList<string>', ScalarList::fromIterable($iterable));
        testPsalmType('Kuria\Collections\ScalarList<int>', ScalarList::collect($int, $int));
        testPsalmType('Kuria\Collections\ScalarList<string>', ScalarList::explode('foo,bar', ','));

        // PHPStan doesn't support @return static<TValue>
        testExactPHPStanType(ScalarList::class, ScalarList::fromIterable($iterable));
        testExactPHPStanType(ScalarList::class, ScalarList::collect($int, $int));
    }

    /**
     * @param ScalarList<string> $strings
     */
    function testReadApi(ScalarList $strings): void
    {
        testType('list<string>', $strings->toArray());
        testType('bool', $strings->isEmpty());
        testType('bool', $strings->has(5));
        testType('bool', $strings->contains('foo'));
        testType('bool', $strings->contains(123)); // should not fail for other types
        testType('Kuria\Maybe\Maybe<non-negative-int>', $strings->find('foo'));
        testType('Kuria\Maybe\Maybe<non-negative-int>', $strings->find(123)); // should not fail for other types
        testType('Kuria\Maybe\Maybe<string>', $strings->findUsing(fn (string $v) => $v === 'foo'));
        testType('Kuria\Maybe\Maybe<non-negative-int>', $strings->findIndexUsing(fn (string $v) => $v === 'foo'));
        testType('bool', $strings->any(fn (string $v) => $v === 'foo'));
        testType('bool', $strings->all(fn (string $v) => $v === 'foo'));
        testType('Kuria\Maybe\Maybe<string>', $strings->get(5));
        testType('Kuria\Maybe\Maybe<string>', $strings->first());
        testType('Kuria\Maybe\Maybe<string>', $strings->last());
        testType('Kuria\Maybe\Maybe<0>', $strings->firstIndex());
        testType('Kuria\Maybe\Maybe<non-negative-int>', $strings->lastIndex());
        testType('Kuria\Maybe\Maybe<string>', $strings->random());
        testType('Kuria\Maybe\Maybe<non-negative-int>', $strings->randomIndex());
    }

    /**
     * @param ScalarList<string> $strings
     */
    function testWriteApi(ScalarList $strings): void
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
     * @param ScalarList<string> $strings
     * @param ScalarList<string> $otherStrings
     * @param ScalarList<int|float> $numbers
     */
    function testTransformations(
        ScalarList $strings,
        ScalarList $otherStrings,
        ScalarList $numbers,
        string $string,
        int $int,
        bool $bool,
    ): void {
        testType('string|null', $strings->reduce(fn (?string $result, string $v) => ($result ?? '') . $v . "\n"));
        testType('int|float', $numbers->sum());
        testType('int|float', $numbers->product());
        testType('string', $strings->implode(','));
        testType('Kuria\Collections\ScalarList<string>&static', $strings->slice(0, 3));
        testPsalmType('Kuria\Collections\ObjectList<Kuria\Collections\ScalarList<string>&static>', $strings->chunk(10));
        testPsalmType('Kuria\Collections\ObjectList<Kuria\Collections\ScalarList<string>&static>', $strings->split(2));
        testType('Kuria\Collections\ScalarList<string>&static', $strings->reverse());
        testType('Kuria\Collections\ScalarList<string>&static', $strings->shuffle());
        testType('Kuria\Collections\ScalarList<string>&static', $strings->pick(3));
        testType('Kuria\Collections\ScalarList<string>&static', $strings->filter(fn (string $v) => $v !== 'foo'));
        testType('Kuria\Collections\Collection<non-negative-int>', $strings->apply(strlen(...)));
        $strings->walk(fn (string $v) => \var_dump($v));
        testType('Kuria\Collections\Collection<string|int|bool>', $strings->merge([$int, $int], [$bool]));
        testType('Kuria\Collections\ScalarList<string>&static', $strings->unique());
        testType('Kuria\Collections\ScalarList<string>&static', $strings->intersect($otherStrings));
        testType('Kuria\Collections\ScalarList<string>&static', $strings->intersectUsing(\strcmp(...), $otherStrings));
        testType('Kuria\Collections\ScalarList<string>&static', $strings->diff($otherStrings));
        testType('Kuria\Collections\ScalarList<string>&static', $strings->diffUsing(\strcmp(...), $otherStrings));
        testType('Kuria\Collections\ScalarList<string>&static', $strings->sort());
        testType('Kuria\Collections\ScalarList<string>&static', $strings->sortBy(\strcmp(...)));
        testPsalmType('Kuria\Collections\ObjectMap<int, Kuria\Collections\ScalarList<string>&static>', $strings->group(fn (int $i, string $v) => $int));
        testPsalmType('Kuria\Collections\ObjectMap<string, Kuria\Collections\ScalarList<string>&static>', $strings->group(fn (int $i, string $v) => $string));
        testType('Kuria\Collections\ScalarMap<string, string>', $strings->map(fn (int $i, string $v) => $string));
        testType('Kuria\Collections\ScalarMap<int, string>', $strings->map(fn (int $i, string $v) => $int));
        testType('Kuria\Collections\Map<string, int>', $strings->buildMap(fn (int $i, string $v) => yield $string => $int));
        testType('Kuria\Collections\Map<int, string>', $strings->buildMap(fn (int $i, string $v) => yield $int => $string));
        testType('Kuria\Collections\ScalarMap<non-negative-int, string>', $strings->toMap());

        // PHPStan doesn't support @return containing static<T>
        testExactPHPStanType('Kuria\Collections\ObjectList<Kuria\Collections\Collection<string>>', $strings->chunk(10));
        testExactPHPStanType('Kuria\Collections\ObjectList<Kuria\Collections\Collection<string>>', $strings->split(2));
        testExactPHPStanType('Kuria\Collections\ObjectMap<int, Kuria\Collections\Collection<string>>', $strings->group(fn (int $i, string $v) => $int));
        testExactPHPStanType('Kuria\Collections\ObjectMap<string, Kuria\Collections\Collection<string>>', $strings->group(fn (int $i, string $v) => $string));
    }

    /**
     * @param ScalarList<int> $ints
     */
    function testTypeCast(ScalarList $ints): void
    {
        testType('Kuria\Collections\Collection', $ints->as(Collection::class));
    }

    /**
     * @param ScalarList<string> $strings
     */
    function testPhpInterfaces(ScalarList $strings): void
    {
        // ArrayAccess
        testType('bool', isset($strings[0]));
        testType('string|null', $strings[0]);
        $strings[] = 'bar';
        $strings[2] = 'baz';
        unset($strings[1]);
        testType('Kuria\Collections\ScalarList<string>', $strings);

        // Countable
        testType('non-negative-int', $strings->count());

        // Traversable
        testType('Traversable<non-negative-int, string>', $strings->getIterator());
    }
}
