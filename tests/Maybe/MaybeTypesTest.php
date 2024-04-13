<?php declare(strict_types=1);

namespace Kuria\Maybe;

use function Kuria\Tools\testExactPsalmType;
use function Kuria\Tools\testPHPStanType;
use function Kuria\Tools\testType;

class MaybeTypesTest
{
    function testBasicTypes(?string $value): void
    {
        $maybe = Maybe::fromNullable($value);

        testType('Kuria\Maybe\Maybe<string>', $maybe);
        testType('string', $maybe->expect('a string'));
        testType('string', $maybe->unwrap());
        testType('string|null', $maybe->toNullable());
    }

    /**
     * @param Maybe<string> $maybe
     */
    function testIsSome(Maybe $maybe): void
    {
        if ($maybe->isSome()) {
            testType('Kuria\Maybe\Some<string>', $maybe);
        } else {
            testExactPsalmType('Kuria\Maybe\Maybe<string>|Kuria\Maybe\None', $maybe); // https://github.com/vimeo/psalm/issues/10851
            testPHPStanType(None::class, $maybe);
        }
    }

    /**
     * @param Maybe<string> $maybe
     */
    function testIsNone(Maybe $maybe): void
    {
        if ($maybe->isNone()) {
            testType(None::class, $maybe);
        } else {
            testExactPsalmType('Kuria\Maybe\Maybe<string>|Kuria\Maybe\Some<string>', $maybe); // https://github.com/vimeo/psalm/issues/10851
            testPHPStanType('Kuria\Maybe\Some<string>', $maybe);
        }
    }

    /**
     * @param Maybe<string> $maybe
     */
    function testWith(Maybe $maybe): void
    {
        testType(
            'string',
            $maybe
                ->with('foo', 3)
                // @phpstan-ignore argument.type (https://github.com/phpstan/phpstan/issues/8214)
                ->andThen(fn (string $str, string $suffix, int $repeat) => $str . \str_repeat($suffix, $repeat))
                ->unwrap(),
        );
    }

    /**
     * @param Maybe<string> $maybe
     */
    function testConditionals(Maybe $maybe): void
    {
        testType(
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
     * @param callable(string):Maybe<array{foo: int, bar: int}> $decoder
     * @param callable(string):void $logger
     */
    function testCallbacks(
        callable $storageA,
        callable $storageB,
        callable $decoder,
        callable $logger,
    ): void {
        testType(
            'int',
            $storageA('some_key')
                ->orDo(fn () => $logger('not found in storage A'))
                ->orElse(fn () => $storageB('some_key'))
                ->orDo(fn () => $logger('not found in storage B'))
                ->andThen($decoder(...))
                ->andDo(fn (array $record) => $logger('decoding success: ' . $record['foo']))
                ->andThen(fn (array $record) => $record['bar'])
                ->do(fn (Maybe $result) => $logger('final result is ' . $result::class))
                ->unwrap(),
        );
    }

    /**
     * @param Maybe<string> $maybe
     */
    function testAutoBoxing(Maybe $maybe): void
    {
        testType(
            'non-empty-string',
            $maybe
                ->or('world')
                ->andThen(fn (string $str) => "hello {$str}")
                ->unwrap(),
        );

        testType(
            'bool',
            $maybe
                ->and(true)
                ->orElse(fn () => false)
                ->unwrap(),
        );
    }
}
