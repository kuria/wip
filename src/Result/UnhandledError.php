<?php declare(strict_types=1);

namespace Kuria\Result;

class UnhandledError extends \LogicException
{
    /**
     * @param Error<mixed> $error
     */
    function __construct(
        string $message,
        private readonly Error $error,
    ) {
        parent::__construct($message);
    }

    /**
     * @return Error<mixed>
     */
    function getError(): Error
    {
        return $this->error;
    }
}
