<?php declare(strict_types=1);

namespace Kuria\Psalm;

/**
 * Assert that the inferred type matches the expected type
 *
 * This is an easier-to-use alternative to the "psalm-check-type" annotation for tests.
 */
function assertType(string $expectedType, mixed $value): void
{}
