<?php declare(strict_types=1);

namespace Kuria\Maybe;

use function Kuria\Psalm\assertType;

class MaybeTypesTest
{
    function testBasicTypes(?string $value): void
    {
        $maybe = Maybe::fromNullable($value);

        assertType('Kuria\Maybe\Maybe<string>', $maybe);
        assertType('string', $maybe->expect('a string'));
        assertType('string', $maybe->unwrap());
        assertType('string|null', $maybe->toNullable());
    }

    /**
     * @param Maybe<string> $maybe
     */
    function testIsSome(Maybe $maybe): void
    {
        if ($maybe->isSome()) {
            assertType('Kuria\Maybe\Some<string>', $maybe);
        } /*else { // https://github.com/vimeo/psalm/issues/10851
            assertType(None::class, $maybe);
        }*/
    }

    /**
     * @param Maybe<string> $maybe
     */
    function testIsNone(Maybe $maybe): void
    {
        if ($maybe->isNone()) {
            assertType(None::class, $maybe);
        } /*else { // https://github.com/vimeo/psalm/issues/10851
            assertType('Kuria\Maybe\Some<string>', $maybe);
        }*/
    }

    /**
     * @param Maybe<string> $maybe
     */
    function testWith(Maybe $maybe): void
    {
        assertType(
            'string',
            $maybe
                ->with('foo', 3)
                ->andThen(fn (string $str, string $suffix, int $repeat) => $str . \str_repeat($suffix, $repeat))
                ->unwrap(),
        );
    }

    /**
     * @param Maybe<string> $maybe
     */
    function testConditionals(Maybe $maybe): void
    {
        assertType(
            'bool',
            $maybe
                ->and(new Some(true))
                ->or(new Some(false))
                ->unwrap(),
        );
    }

    /**
     * @param callable(string):Maybe<string> $storageA
     * @param callable(string):Maybe<string> $storageB
     * @param callable(string):Maybe<object{foo: int, bar: int}> $decoder
     * @param callable(string):void $logger
     */
    function testCallbacks(
        callable $storageA,
        callable $storageB,
        callable $decoder,
        callable $logger,
    ): void {
        assertType(
            'int',
            $storageA('some_key')
                ->orDo(fn () => $logger('not found in storage A'))
                ->orElse(fn () => $storageB('some_key'))
                ->orDo(fn () => $logger('not found in storage B'))
                ->andThen($decoder(...))
                ->andDo(fn (object $record) => ++$record->foo)
                ->andThen(fn (object $record) => $record->foo)
                ->do(fn (Maybe $result) => $logger('final result is ' . $result::class))
                ->unwrap(),
        );
    }

    /**
     * @param Maybe<string> $maybe
     */
    function testAutoBoxing(Maybe $maybe): void
    {
        assertType(
            'non-empty-string',
            $maybe
                ->or('world')
                ->andThen(fn (string $str) => "hello {$str}")
                ->unwrap(),
        );

        assertType(
            'bool',
            $maybe
                ->and(true)
                ->orElse(fn () => false)
                ->unwrap(),
        );
    }
}
