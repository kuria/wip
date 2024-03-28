<?php declare(strict_types=1);

namespace Kuria\Result;

use Kuria\Maybe\{Some, None};

/**
 * @template-covariant TError
 * @extends Result<never, TError>
 */
final class Error extends Result
{
    /**
     * @param TError $error
     * @param self<mixed>|null $previous
     */
    function __construct(
        private readonly mixed $error,
        private ?self $previous = null,
        mixed ...$context,
    ) {
        $this->context = $context;
    }

    /**
     * This is NOT an OK result
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
     */
    function andDo(\Closure $callback): Result
    {
        return $this;
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
    function or(Result $result): Result
    {
        return $this->propagate($result);
    }

    /**
     * Call the given callback with the error and return the next result
     *
     * @template TNextValue
     * @template TNextError
     *
     * @param \Closure(TError, mixed...):Result<TNextValue, TNextError> $callback
     * @return Result<TNextValue, TNextError>
     */
    function orElse(\Closure $callback): Result
    {
        return $this->propagate($callback($this->error, ...$this->context));
    }

    /**
     * Call the given callback with the error and return $this
     *
     * The callback's return value is ignored.
     */
    function orDo(\Closure $callback): Result
    {
        $callback($this->error, ...$this->context);

        return $this;
    }

    /**
     * If the error is an instance of $errorClass, call the handler and return the next result, otherwise return $this
     *
     * @template TNextValue
     * @template TNextError
     * @template TCaughtError of TError
     *
     * @param class-string<TCaughtError> $errorClass
     * @param \Closure(TCaughtError, mixed...):Result<TNextValue, TNextError> $handler
     * @return Result<TNextValue, TError|TNextError>
     */
    function catch(string $errorClass, \Closure $handler): Result
    {
        if ($this->error instanceof $errorClass) {
            /** @var TCaughtError $this->error */
            return $handler($this->error);
        }

        return $this;
    }

    /**
     * If the error is identical to $errorValue, call the handler and return the next result, otherwise return $this
     *
     * @template TNextValue
     * @template TNextError
     * @template THandledError of TError
     *
     * @param THandledError $errorValue
     * @param \Closure(THandledError, mixed...):Result<TNextValue, TNextError> $handler
     * @return Result<TNextValue, TError|TNextError>
     */
    function handle(mixed $errorValue, \Closure $handler): Result
    {
        if ($this->error === $errorValue) {
            return $handler($this->error);
        }

        return $this;
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
        if (\is_object($this->error)) {
            $message = $this->error::class;

            if ($this->error instanceof \UnitEnum) {
                $message .= '::' . $this->error->name;
            }
        } elseif (\is_scalar($this->error)) {
            $message = (string) $this->error;
        } else {
            $message = \get_debug_type($this->error);
        }

        throw new UnhandledError($message, $this);
    }

    /**
     * Return the error
     */
    function unwrapError(): mixed
    {
        return $this->error;
    }

    /**
     * Get the previous error, if any
     *
     * @return self<mixed>|null
     */
    function getPrevious(): ?self
    {
        return $this->previous;
    }

    /**
     * If the given result is an error, propagate this error into its $previous property (if not set)
     *
     * @template TResultValue
     * @template TResultError
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
