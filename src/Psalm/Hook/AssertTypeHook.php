<?php declare(strict_types=1);

namespace Kuria\Psalm\Hook;

use Kuria\Psalm\CodeIssue\FailedTypeAssertion;
use Kuria\Psalm\CodeIssue\InvalidTypeAssertion;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\StatementsSource;
use Psalm\Type;
use Psalm\Type\Union;

/**
 * Handle the {@see \Kuria\Psalm\assertType()} function
 */
class AssertTypeHook implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        $expr = $event->getExpr();

        if ($expr instanceof FuncCall && $expr->name->getAttribute('resolvedName') === 'Kuria\Psalm\assertType') {
            self::assertType($expr, $event->getStatementsSource());
        }

        return null;
    }

    private static function assertType(FuncCall $call, StatementsSource $source): void
    {
        if (\count($call->args) !== 2) {
            return;
        }

        /** @psalm-var Arg[] $call->args */

        $expectedType = self::getExpectedType($call->args[0], $source);

        if ($expectedType === null) {
            return;
        }

        $actualType = self::getActualType($call->args[1], $source);

        if ($actualType === null) {
            IssueBuffer::accepts(new FailedTypeAssertion(
                'Expected type: \'' . $expectedType . '\', actual type could not be inferred',
                new CodeLocation($source, $call->args[1]),
            ));

            return;
        }

        if ($expectedType->getId() !== $actualType->getId()) {
            IssueBuffer::accepts(new FailedTypeAssertion(
                'Expected type: \'' . $expectedType->getId() . '\', actual type: \'' . $actualType->getId() . '\'',
                new CodeLocation($source, $call->args[1]),
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
                IssueBuffer::accepts(new InvalidTypeAssertion(
                    'Unresolved ' . $arg->getType(),
                    new CodeLocation($source, $arg),
                ));

                return null;
            }
        } else {
            IssueBuffer::accepts(new InvalidTypeAssertion(
                'Expected type must be a literal string or a static ::class fetch, got ' . $arg->value->getType(),
                new CodeLocation($source, $arg),
            ));

            return null;
        }

        return Type::parseString($typeString);
    }

    private static function getActualType(Arg $arg, StatementsSource $source): ?Union
    {
        return $source->getNodeTypeProvider()->getType($arg->value);
    }
}
