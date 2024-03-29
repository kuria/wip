<?php declare(strict_types=1);

namespace Kuria\Tools\Psalm\Hook;

use Kuria\Tools\Constants;
use Kuria\Tools\Psalm\CodeIssue\FailedTypeTest;
use Kuria\Tools\Psalm\CodeIssue\InvalidTypeTest;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Psalm\CodeLocation;
use Psalm\Internal\Type\Comparator\UnionTypeComparator;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\StatementsSource;
use Psalm\Type;
use Psalm\Type\Union;

class TestTypeHook implements AfterExpressionAnalysisInterface
{
    /** Function => exact? */
    private const array TEST_FUNC_MAP = [
        Constants::TEST_TYPE_FN => false,
        Constants::TEST_PSALM_TYPE_FN => false,
        Constants::TEST_EXACT_TYPE_FN => true,
        Constants::TEST_EXACT_PSALM_TYPE_FN => true,
    ];

    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        $expr = $event->getExpr();

        /** @psalm-suppress MixedAssignment */
        if (
            $expr instanceof FuncCall
            && \is_string($functionName = $expr->name->getAttribute('resolvedName'))
            && isset(self::TEST_FUNC_MAP[$functionName])
            && \count($expr->args) === 2
        ) {
            /** @psalm-var Arg[] $expr->args */
            $source = $event->getStatementsSource();

            $expectedType = self::getExpectedType($expr->args[0], $source);

            if ($expectedType === null) {
                return null;
            }

            $actualType = self::getActualType($expr->args[1], $source);

            if ($actualType === null) {
                return null;
            }

            if (self::TEST_FUNC_MAP[$functionName]) {
                self::testExactType($expectedType, $actualType, $expr->args[1], $source);
            } else {
                self::testType($expectedType, $actualType, $expr->args[1], $source);
            }
        }

        return null;
    }

    private static function testType(Union $expected, Union $actual, Node $node, StatementsSource $source): void
    {
        /** @psalm-suppress InternalClass,InternalMethod */
        if (!UnionTypeComparator::isContainedBy($source->getCodebase(), $actual, $expected)) {
            IssueBuffer::accepts(new FailedTypeTest(
                'Expected type: \'' . $expected->getId() . '\', actual: \'' . $actual->getId() . '\'',
                new CodeLocation($source, $node),
            ));
        }
    }

    private static function testExactType(Union $expected, Union $actual, Node $node, StatementsSource $source): void
    {
        if ($expected->getId() !== $actual->getId()) {
            IssueBuffer::accepts(new FailedTypeTest(
                'Expected exact type: \'' . $expected->getId() . '\', actual: \'' . $actual->getId() . '\'',
                new CodeLocation($source, $node),
            ));
        }
    }

    private static function getExpectedType(Arg $arg, StatementsSource $source): ?Union
    {
        if ($arg->value instanceof String_) {
            $typeString = $arg->value->value;
        } elseif ($arg->value instanceof ClassConstFetch && $arg->value->name instanceof Identifier && $arg->value->name->name === 'class') {
            /** @var string|null $typeString */
            $typeString = $arg->value->class->getAttribute('resolvedName');

            if ($typeString === null) {
                IssueBuffer::accepts(new InvalidTypeTest(
                    'Unresolved ' . $arg->getType(),
                    new CodeLocation($source, $arg),
                ));

                return null;
            }
        } else {
            IssueBuffer::accepts(new InvalidTypeTest(
                'Expected type must be a literal string or a static ::class fetch, got ' . $arg->value->getType(),
                new CodeLocation($source, $arg),
            ));

            return null;
        }

        return Type::parseString($typeString);
    }

    private static function getActualType(Arg $arg, StatementsSource $source): ?Union
    {
        $type = $source->getNodeTypeProvider()->getType($arg->value);

        if ($type === null) {
            IssueBuffer::accepts(new FailedTypeTest(
                'Could not infer actual type',
                new CodeLocation($source, $arg),
            ));
        }

        return $type;
    }
}
