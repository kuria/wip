<?php declare(strict_types=1);

namespace Kuria\Result;

use Kuria\Maybe\{Some, None};

/**
 * @template-covariant TValue
 * @extends Result<TValue, never>
 */
final class Ok extends Result
{
    /**
     * @param TValue $value
     */
    function __construct(private readonly mixed $value, mixed ...$context)
    {
        $this->context = $context;
    }

    /**
     * This is an OK result
     */
    function isOk(): true
    {
        return true;
    }

    /**
     * This is NOT an error
     */
    function isError(): false
    {
        return false;
    }

    /**
     * Return the given result
     *
     * @template TNextValue
     * @template TNextError
     *
     * @param Result<TNextValue, TNextError> $result
     * @return Result<TNextValue, TNextError>
     */
    function and(Result $result): Result
    {
        return $result;
    }

    /**
     * Call the given callback with the value and return the next result
     *
     * @template TNextValue
     * @template TNextError
     *
     * @param \Closure(TValue, mixed...):Result<TNextValue, TNextError> $callback
     * @return Result<TNextValue, TNextError>
     */
    function andThen(\Closure $callback): Result
    {
        return $callback($this->value, ...$this->context);
    }

    /**
     * Call the given callback with the value and return $this
     *
     * The callback's return value is ignored.
     */
    function andDo(\Closure $callback): Result
    {
        $callback($this->value, ...$this->context);

        return $this;
    }

    /**
     * Return $this
     *
     * @return $this
     */
    function or(Result $result): Result
    {
        return $this;
    }

    /**
     * Return $this (callback is not called)
     *
     * @return $this
     */
    function orElse(\Closure $callback): Result
    {
        return $this;
    }

    /**
     * Return $this (callback is not called)
     */
    function orDo(\Closure $callback): Result
    {
        return $this;
    }

    /**
     * Return $this (handler is not called)
     *
     * @return $this
     */
    function catch(string $errorClass, \Closure $handler): Result
    {
        return $this;
    }

    /**
     * Return $this (handler is not called)
     *
     * @return $this
     */
    function handle(mixed $errorValue, \Closure $handler): Result
    {
        return $this;
    }

    /**
     * Get the value as a maybe type
     *
     * @return Some<TValue>
     */
    function getValue(): Some
    {
        return new Some($this->value);
    }

    /**
     * Return none as there is no error
     */
    function getError(): None
    {
        return new None();
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
     * Throws as there is no error
     */
    function unwrapError(): never
    {
        throw new \LogicException('There is no error');
    }
}
