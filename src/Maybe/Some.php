<?php declare(strict_types=1);

namespace Kuria\Maybe;

/**
 * @template-covariant T
 * @extends Maybe<T>
 */
final class Some extends Maybe
{
    /**
     * @param T $value
     */
    function __construct(
        private readonly mixed $value,
        mixed ...$context,
    ) {
        $this->context = $context;
    }

    /**
     * This is some
     */
    function isSome(): true
    {
        return true;
    }

    /**
     * This is NOT none
     */
    function isNone(): false
    {
        return false;
    }

    /**
     * Return the given maybe
     *
     * @template TNext
     *
     * @param Maybe<TNext>|TNext $value
     * @return Maybe<TNext|never>
     */
    function and(mixed $value): Maybe
    {
        return $value instanceof Maybe ? $value : new Some($value);
    }

    /**
     * Call the given callback with the value and return the next maybe
     *
     * @template TNext
     *
     * @param \Closure(T, mixed...):(Maybe<TNext>|TNext) $callback
     * @return Maybe<TNext>
     */
    function andThen(\Closure $callback): Maybe
    {
        $result = $callback($this->value, ...$this->context);

        return $result instanceof Maybe ? $result : new Some($result);
    }

    /**
     * Call the given callback with the value and return $this
     *
     * @param \Closure(T, mixed...):mixed $callback
     * @return $this
     */
    function andDo(\Closure $callback): Maybe
    {
        $callback($this->value, ...$this->context);

        return $this;
    }

    /**
     * If this maybe is none, return the given maybe, otherwise return $this
     *
     * @template TNext
     *
     * @param Maybe<TNext>|TNext $value
     * @return Maybe<T|TNext>
     */
    function or(mixed $value): Maybe
    {
        return $this;
    }

    /**
     * Return $this
     *
     * @return $this
     */
    function orElse(\Closure $callback): Maybe
    {
        return $this;
    }

    /**
     * Return $this (callback is not called)
     *
     * @return $this
     */
    function orDo(\Closure $callback): Maybe
    {
        return $this;
    }

    /**
     * Return the value
     */
    function expect(string $message): mixed
    {
        return $this->value;
    }

    /**
     * Return the value
     */
    function unwrap(): mixed
    {
        return $this->value;
    }

    /**
     * Return the value
     *
     * @return T
     */
    function toNullable(): mixed
    {
        return $this->value;
    }
}
