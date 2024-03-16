<?php declare(strict_types=1);

namespace Kuria\Result;

use Kuria\Maybe\{Some, None};

/**
 * @template-covariant TError of object
 * @extends Result<never, TError>
 */
final class Error extends Result
{
    /**
     * @param TError $error
     * @param self<object>|null $previous
     */
    function __construct(
        private readonly object $error,
        private ?self $previous = null,
        mixed ...$context,
    ) {
        $this->context = $context;
    }

    /**
     * This is NOT an ok result
     */
    function isOk(): false
    {
        return false;
    }

    /**
     * This is an error result
     */
    function isError(): true
    {
        return true;
    }

    /**
     * Return $this
     *
     * @return $this
     */
    function and(Result $result): Result
    {
        return $this;
    }

    /**
     * Return $this (callback is not called)
     *
     * @return $this
     */
    function andThen(\Closure $callback): Result
    {
        return $this;
    }

    /**
     * Return $this (callback is not called)
     *
     * @return $this
     */
    function andDo(\Closure $callback): Result
    {
        return $this;
    }

    /**
     * Return the given result
     *
     * @template TNextValue
     * @template TNextError of object
     *
     * @param Result<TNextValue, TNextError> $result
     * @return Result<TNextValue, TNextError>
     */
    function or(Result $result): Result
    {
        return $this->propagate($result);
    }

    /**
     * Call the given callback with the error object and return the next result
     *
     * @template TNextValue
     * @template TNextError of object
     *
     * @param \Closure(TError, mixed...):Result<TNextValue, TNextError> $callback
     * @return Result<TNextValue, TNextError>
     */
    function orElse(\Closure $callback): Result
    {
        return $this->propagate($callback($this->error, ...$this->context));
    }

    /**
     * Call the given callback with the error object and return $this
     *
     * @param \Closure(TError, mixed...):mixed $callback
     * @return $this
     */
    function orDo(\Closure $callback): Result
    {
        $callback($this->error, ...$this->context);

        return $this;
    }

    /**
     * If the given error type matches, call the handler with the error object and return the result, otherwise return $this
     *
     * @template TNextValue
     * @template TNextError of object
     * @template THandledError of object
     *
     * @param class-string<THandledError>|THandledError $errorType
     * @param \Closure(THandledError, mixed...):Result<TNextValue, TNextError> $handler
     * @return Result<TNextValue, TNextError>|$this
     */
    function handle(string|object $errorType, \Closure $handler): Result
    {
        if (\is_string($errorType)) {
            if (!$this->error instanceof $errorType) {
                return $this;
            }
        } elseif ($this->error !== $errorType) {
            return $this;
        }

        return $this->propagate($handler($this->error));
    }

    /**
     * Return none as there is no value
     */
    function getValue(): None
    {
        return new None();
    }

    /**
     * Get the error as a maybe type
     *
     * @return Some<TError>
     */
    function getError(): Some
    {
        return new Some($this->error);
    }

    /**
     * Throws as there is no value
     */
    function expect(string $message): mixed
    {
        throw new UnhandledError($message, $this);
    }

    /**
     * Throws as there is no value
     */
    function unwrap(): never
    {
        $message = $this->error::class;

        if ($this->error instanceof \UnitEnum) {
            $message .= '::' . $this->error->name;
        }

        throw new UnhandledError($message, $this);
    }

    /**
     * Return the error
     */
    function unwrapError(): object
    {
        return $this->error;
    }

    /**
     * Get the previous error, if any
     *
     * @return self<object>|null
     */
    function getPrevious(): ?self
    {
        return $this->previous;
    }

    /**
     * If the given result is an error, propagate this error into its $previous property (if not set)
     *
     * @template TResultValue
     * @template TResultError of object
     *
     * @param Result<TResultValue, TResultError> $result
     * @return Result<TResultValue, TResultError>
     */
    private function propagate(Result $result): Result
    {
        if ($result instanceof self) {
            $result->previous ??= $this;
        }

        return $result;
    }
}
