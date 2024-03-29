<?php declare(strict_types=1);

namespace Kuria\Tools;

abstract class Constants
{
    const string TEST_TYPE_FN = __NAMESPACE__ . '\\testType';
    const string TEST_PSALM_TYPE_FN = __NAMESPACE__ . '\\testPsalmType';
    const string TEST_PHPSTAN_TYPE_FN = __NAMESPACE__ . '\\testPHPStanType';
    const string TEST_EXACT_TYPE_FN = __NAMESPACE__ . '\\testExactType';
    const string TEST_EXACT_PSALM_TYPE_FN = __NAMESPACE__ . '\\testExactPsalmType';
    const string TEST_EXACT_PHPSTAN_TYPE_FN = __NAMESPACE__ . '\\testExactPHPStanType';
}
