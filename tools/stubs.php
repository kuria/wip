<?php declare(strict_types=1);

namespace Kuria\Tools;

/**
 * Assert that the inferred type is compatible with the expected type
 */
function testType(string $expectedType, mixed $value): void
{}

/**
 * Assert that the inferred type is compatible with the expected type (Psalm only)
 */
function testPsalmType(string $expectedType, mixed $value): void
{}

/**
 * Assert that the inferred type is compatible with the expected type (PHPStan only)
 */
function testPHPStanType(string $expectedType, mixed $value): void
{}

/**
 * Assert that the inferred type is identical to the expected type
 */
function testExactType(string $expectedType, mixed $value): void
{}

/**
 * Assert that the inferred type is identical to the expected type (Psalm only)
 */
function testExactPsalmType(string $expectedType, mixed $value): void
{}

/**
 * Assert that the inferred type is identical to the expected type (PHPStan only)
 */
function testExactPHPStanType(string $expectedType, mixed $value): void
{}
