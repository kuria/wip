<?php declare(strict_types=1);

namespace Kuria\Tools\PHPStan;

use Kuria\Tools\Constants;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use PhpParser\Node;

/**
 * @implements Rule<Node\Expr\FuncCall>
 */
class TestTypeRule implements Rule
{
    /** Function => exact? */
    private const array TEST_FUNC_MAP = [
        Constants::TEST_TYPE_FN => false,
        Constants::TEST_PHPSTAN_TYPE_FN => false,
        Constants::TEST_EXACT_TYPE_FN => true,
        Constants::TEST_EXACT_PHPSTAN_TYPE_FN => true,
    ];

    function __construct(
        private ReflectionProvider $reflectionProvider,
        private TypeStringResolver $typeStringResolver,
        private RuleLevelHelper $ruleLevelHelper,
    ) {}

    function getNodeType(): string
    {
        return Node\Expr\FuncCall::class;
    }

    function processNode(Node $node, Scope $scope): array
    {
        if (
            $node->name instanceof Node\Name
            && $this->reflectionProvider->hasFunction($node->name, $scope)
            && isset(self::TEST_FUNC_MAP[$functionName = $this->reflectionProvider->getFunction($node->name, $scope)->getName()])
            && \count($args = $node->getArgs()) === 2
        ) {
            $errors = [];

            $expectedType = $this->getExpectedType($args, $scope, $errors);

            if ($expectedType === null) {
                return $errors;
            }

            $actualType = $scope->getType($args[1]->value);

            if (self::TEST_FUNC_MAP[$functionName]) {
                $this->testExactType($expectedType, $actualType, $errors);
            } else {
                $this->testType($expectedType, $actualType, $errors);
            }

            return $errors;
        }

        return [];
    }

    /**
     * @param list<IdentifierRuleError> $errors
     */
    private function testType(Type $expected, Type $actual, array &$errors): void
    {
        if (!$this->ruleLevelHelper->accepts($expected, $actual, true)) {
            $verbosityLevel = VerbosityLevel::getRecommendedLevelByType($expected, $actual);

            $errors[] = RuleErrorBuilder::message("Expected type {$expected->describe($verbosityLevel)}, actual: {$actual->describe($verbosityLevel)}'")
                ->nonIgnorable()
                ->identifier('kuria.failedTypeTest')
                ->build();
        }
    }

    /**
     * @param list<IdentifierRuleError> $errors
     */
    private function testExactType(Type $expected, Type $actual, array &$errors): void
    {
        $expectedDescr = $expected->describe(VerbosityLevel::precise());
        $actualDescr = $actual->describe(VerbosityLevel::precise());

        if ($expectedDescr !== $actualDescr) {
            $errors[] = RuleErrorBuilder::message("Expected exact type {$expectedDescr}, actual: {$actualDescr}")
                ->nonIgnorable()
                ->identifier('kuria.failedTypeTest')
                ->build();
        }
    }

    /**
     * @param Node\Arg[] $args
     * @param list<IdentifierRuleError> $errors
     */
    private function getExpectedType(array $args, Scope $scope, array &$errors): ?Type
    {
        $expectedTypeStrings = $scope->getType($args[0]->value)->getConstantStrings();

        if (\count($expectedTypeStrings) !== 1) {
            $errors[] = RuleErrorBuilder::message('Expected type must be a literal string.')
                ->nonIgnorable()
                ->identifier('kuria.invalidTypeTest')
                ->build();

            return null;
        }

        $expectedTypeString = $this->normalizeExpectedType($expectedTypeStrings[0]->getValue());

        return $this->typeStringResolver->resolve($expectedTypeString);
    }

    /**
     * Make having the same assertions for both Psalm and PHPStan easier
     */
    private function normalizeExpectedType(string $type): string
    {
        return \preg_replace('{&static\b}', '', $type);
    }
}
