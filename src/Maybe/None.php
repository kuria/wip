<?php declare(strict_types=1);

namespace Kuria\Maybe;

/**
 * @extends Maybe<never>
 */
final class None extends Maybe
{
    /**
     * This is NOT some
     */
    function isSome(): false
    {
        return false;
    }

    /**
     * This is none
     */
    function isNone(): true
    {
        return true;
    }

    /**
     * Return $this
     *
     * @return $this
     */
    function and(mixed $value): Maybe
    {
        return $this;
    }

    /**
     * Return $this (callback is not called)
     *
     * @return $this
     */
    function andThen(\Closure $callback): Maybe
    {
        return $this;
    }

    /**
     * Return $this (callback is not called)
     *
     * @return $this
     */
    function andDo(\Closure $callback): Maybe
    {
        return $this;
    }

    /**
     * Return the given maybe
     *
     * @template TNext
     *
     * @param Maybe<TNext>|TNext $value
     * @return Maybe<TNext>
     */
    function or(mixed $value): Maybe
    {
        return $value instanceof Maybe ? $value : new Some($value);
    }

    /**
     * Call the given callback and return the next maybe
     *
     * @template TNext
     *
     * @param \Closure(mixed...):(Maybe<TNext>|TNext) $callback
     * @return Maybe<TNext>
     */
    function orElse(\Closure $callback): Maybe
    {
        $result = $callback(...$this->context);

        return $result instanceof Maybe ? $result : new Some($result);
    }

    /**
     * Call the given callback and return $this
     *
     * @param \Closure(mixed...):mixed $callback
     * @return $this
     */
    function orDo(\Closure $callback): Maybe
    {
        $callback(...$this->context);

        return $this;
    }

    /**
     * Throws as there is no value
     */
    function expect(string $message): mixed
    {
        throw new \LogicException($message);
    }

    /**
     * Throws as there is no value
     */
    function unwrap(): never
    {
        throw new \LogicException('There is no value');
    }

}
