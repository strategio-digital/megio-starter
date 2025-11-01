<?php

declare(strict_types=1);

namespace App\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

use function array_merge;
use function count;

/**
 * @implements Rule<Node>
 */
class ExplicitBooleanComparisonRule implements Rule
{
    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return list<IdentifierRuleError>
     */
    public function processNode(
        Node $node,
        Scope $scope,
    ): array {
        // Check if/elseif/while/ternary conditions
        if (($node instanceof If_) === true) {
            return $this->checkCondition($node->cond, $scope);
        }

        if (($node instanceof ElseIf_) === true) {
            return $this->checkCondition($node->cond, $scope);
        }

        if (($node instanceof While_) === true) {
            return $this->checkCondition($node->cond, $scope);
        }

        if (($node instanceof Ternary) === true) {
            return $this->checkCondition($node->cond, $scope);
        }

        // Check assert() function calls
        if (($node instanceof FuncCall) === true && ($node->name instanceof Name) === true) {
            $functionName = $node->name->toString();
            if ($functionName === 'assert' && count($node->args) !== 0) {
                $firstArg = $node->args[0];
                if (($firstArg instanceof Arg) === true) {
                    return $this->checkCondition($firstArg->value, $scope);
                }
            }
        }

        return [];
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return list<IdentifierRuleError>
     */
    private function checkCondition(
        Node\Expr $condition,
        Scope $scope,
    ): array {
        // Povolené konstrukce - explicitní porovnání
        if (($condition instanceof Identical || $condition instanceof NotIdentical) === true) {
            return []; // === nebo !== je OK
        }

        // Rekurzivně kontroluj && a || operátory
        if (($condition instanceof BooleanAnd) === true) {
            $leftErrors = $this->checkCondition($condition->left, $scope);
            $rightErrors = $this->checkCondition($condition->right, $scope);
            return array_merge($leftErrors, $rightErrors);
        }

        if (($condition instanceof BooleanOr) === true) {
            $leftErrors = $this->checkCondition($condition->left, $scope);
            $rightErrors = $this->checkCondition($condition->right, $scope);
            return array_merge($leftErrors, $rightErrors);
        }

        if (($condition instanceof BooleanNot) === true) {
            return []; // ! je ošetřen jiným pravidlem
        }

        // Kontrola typu výrazu
        $conditionType = $scope->getType($condition);

        // Pokud je typ boolean, musí být explicitně porovnán
        if ($conditionType->isBoolean()->yes() === true) {
            return [
                RuleErrorBuilder::message(
                    'Boolean value must be compared explicitly using === true or === false.',
                )->identifier('custom.explicitBooleanComparison')->build(),
            ];
        }

        return [];
    }
}
