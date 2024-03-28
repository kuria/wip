<?php declare(strict_types=1);

namespace Kuria\Maybe;

/**
 * @template-covariant T
 */
abstract class Maybe
{
    /** @var mixed[] */
    protected array $context;

    /**
     * @template TValue
     *
     * @param TValue|null $value
     * @return Maybe<TValue>
     */
    static function fromNullable(mixed $value): Maybe
    {
        return $value !== null ? new Some($value) : new None();
    }

    /**
     * See if this maybe is some
     *
     * @psalm-assert-if-true Some<T> $this
     * @psalm-assert-if-false None $this
     * @phpstan-assert-if-true Some<T> $this
     * @phpstan-assert-if-false None $this
     */
    abstract function isSome(): bool;

    /**
     * See if this maybe is none
     *
     * @psalm-assert-if-true None $this
     * @psalm-assert-if-false Some<T> $this
     * @phpstan-assert-if-true None $this
     * @phpstan-assert-if-false Some<T> $this
     */
    abstract function isNone(): bool;

    /**
     * Define context arguments
     *
     * They will be passed as additional arguments to callbacks.
     *
     * @return $this
     */
    function with(mixed ...$context): Maybe
    {
        $this->context = $context;

        return $this;
    }

    /**
     * If this maybe is some, return the given maybe, otherwise return $this
     *
     * @template TNext
     *
     * @param Maybe<TNext>|TNext $value
     * @return Maybe<TNext|never>
     */
    abstract function and(mixed $value): Maybe;

    /**
     * If this maybe is some, call the given callback with the value and return the next maybe, otherwise return $this
     *
     * @template TNext
     *
     * @param \Closure(T, mixed...):(Maybe<TNext>|TNext) $callback
     * @return Maybe<TNext|never>
     */
    abstract function andThen(\Closure $callback): Maybe;

    /**
     * If this maybe is some, call the given callback with the value, and return $this
     *
     * @param \Closure(T, mixed...):mixed $callback
     * @return $this
     */
    abstract function andDo(\Closure $callback): Maybe;

    /**
     * If this maybe is none, return the given maybe, otherwise return $this
     *
     * @template TNext
     *
     * @param Maybe<TNext>|TNext $value
     * @return Maybe<T|TNext>
     */
    abstract function or(mixed $value): Maybe;

    /**
     * If this maybe is none, call the given callback and return the next maybe, otherwise return $this
     *
     * @template TNext
     *
     * @param \Closure(mixed...):(Maybe<TNext>|TNext) $callback
     * @return Maybe<T|TNext>
     */
    abstract function orElse(\Closure $callback): Maybe;

    /**
     * If this maybe is none, call the given callback, and return $this
     *
     * @param \Closure(mixed...):mixed $callback
     * @return $this
     */
    abstract function orDo(\Closure $callback): Maybe;

    /**
     * Call the callback with the current maybe and return $this
     *
     * @param \Closure($this, mixed...):mixed $callback
     * @return $this
     */
    function do(\Closure $callback): Maybe
    {
        $callback($this, ...$this->context);

        return $this;
    }

    /**
     * Get the value or throw with a custom message if this maybe is none
     *
     * @return T
     */
    abstract function expect(string $message): mixed;

    /**
     * Get the value or throw if this maybe is none
     *
     * @return T
     */
    abstract function unwrap(): mixed;

    /**
     * @return T|null
     */
    abstract function toNullable(): mixed;
}
