<?php declare(strict_types=1);

namespace Kuria\Result;

use Kuria\Maybe\Maybe;

/**
 * @template-covariant TValue
 * @template-covariant TError of object
 */
abstract class Result
{
    /** @var mixed[] */
    protected array $context;

    /**
     * See if this is an OK result
     *
     * @phpstan-assert-if-true Ok<TValue> $this
     * @phpstan-assert-if-false Error<TError> $this
     */
    abstract function isOk(): bool;

    /**
     * See if this is an error result
     *
     * @phpstan-assert-if-true Error<TError> $this
     * @phpstan-assert-if-false Ok<TValue> $this
     */
    abstract function isError(): bool;

    /**
     * Define context arguments
     *
     * They will be passed as additional arguments to callbacks.
     *
     * @return $this
     */
    function with(mixed ...$context): Result
    {
        $this->context = $context;

        return $this;
    }

    /**
     * If this is an OK result, return the given result, otherwise return $this
     *
     * @template TNextValue
     * @template TNextError of object
     *
     * @param Result<TNextValue, TNextError> $result
     * @return Result<TNextValue, TNextError>|Error<TError>
     */
    abstract function and(Result $result): Result;

    /**
     * If this is an OK result, call the given callback with the value and return the next result, otherwise return $this
     *
     * @template TNextValue
     * @template TNextError of object
     *
     * @param \Closure(TValue, mixed...):Result<TNextValue, TNextError> $callback
     * @return Result<TNextValue, TNextError>|Error<TError>
     */
    abstract function andThen(\Closure $callback): Result;

    /**
     * If this is an OK result, call the given callback with the value, and return $this
     *
     * @param \Closure(TValue, mixed...):mixed $callback
     * @return $this
     */
    abstract function andDo(\Closure $callback): Result;

    /**
     * If this is an error result, return the given result, otherwise return $this
     *
     * @template TNextValue
     * @template TNextError of object
     *
     * @param Result<TNextValue, TNextError> $result
     * @return Result<TNextValue, TNextError>|Ok<TValue>
     */
    abstract function or(Result $result): Result;

    /**
     * If this is an error result, call the given callback with the error object and return the next result, otherwise return $this
     *
     * @template TNextValue
     * @template TNextError of object
     *
     * @param \Closure(TError, mixed...):Result<TNextValue, TNextError> $callback
     * @return Result<TNextValue, TNextError>|Ok<TValue>
     */
    abstract function orElse(\Closure $callback): Result;

    /**
     * If this is an error result, call the given callback with the error object, and return $this
     *
     * @param \Closure(TError, mixed...):mixed $callback
     * @return $this
     */
    abstract function orDo(\Closure $callback): Result;

    /**
     * If this is an error result and the given error type matches, call the handler with the error object and return the result, otherwise return $this
     *
     * @template TNextValue
     * @template TNextError of object
     * @template THandledError of object
     *
     * @param class-string<THandledError>|THandledError $errorType
     * @param \Closure(THandledError, mixed...):Result<TNextValue, TNextError> $handler
     * @return Result<TNextValue, TNextError>|$this
     */
    abstract function handle(string|object $errorType, \Closure $handler): Result;

    /**
     * Call the callback with the current result and return $this
     *
     * @param \Closure($this, mixed...):mixed $callback
     * @return $this
     */
    function do(\Closure $callback): Result
    {
        $callback($this, ...$this->context);

        return $this;
    }

    /**
     * Get the value as a maybe type, ignoring errors
     *
     * @return Maybe<TValue>
     */
    abstract function getValue(): Maybe;

    /**
     * Get the error as a maybe type, ignoring the value
     *
     * @return Maybe<TError>
     */
    abstract function getError(): Maybe;

    /**
     * Get the value or throw with a custom message if this is an error result
     *
     * @return TValue
     */
    abstract function expect(string $message): mixed;

    /**
     * Get the value or throw if this is an error result
     *
     * @return TValue
     */
    abstract function unwrap(): mixed;

    /**
     * Get the error or throw if this is an OK result
     *
     * @return TError
     */
    abstract function unwrapError(): mixed;
}
