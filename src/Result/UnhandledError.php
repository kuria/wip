<?php declare(strict_types=1);

namespace Kuria\Result;

class UnhandledError extends \LogicException
{
    /**
     * @param Error<object> $error
     */
    function __construct(
        string $message,
        private readonly Error $error,
    ) {
        parent::__construct($message);
    }

    /**
     * @return Error<object>
     */
    function getError(): Error
    {
        return $this->error;
    }
}
