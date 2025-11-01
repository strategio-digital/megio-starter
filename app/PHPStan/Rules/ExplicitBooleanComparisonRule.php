<?php

declare(strict_types=1);

namespace App\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\BooleanType;
use PHPStan\Type\UnionType;

/**
 * @implements Rule<Node>
 */
class ExplicitBooleanComparisonRule implements Rule
{
    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof If_) {
            return $this->checkCondition($node->cond, $scope);
        }

        if ($node instanceof ElseIf_) {
            return $this->checkCondition($node->cond, $scope);
        }

        if ($node instanceof While_) {
            return $this->checkCondition($node->cond, $scope);
        }

        if ($node instanceof Ternary) {
            return $this->checkCondition($node->cond, $scope);
        }

        return [];
    }

    /**
     * @return array<string>
     */
    private function checkCondition(Node\Expr $condition, Scope $scope): array
    {
        // Povolené konstrukce
        if ($condition instanceof Identical || $condition instanceof NotIdentical) {
            return []; // === nebo !== je OK
        }

        if ($condition instanceof BooleanAnd || $condition instanceof BooleanOr) {
            return []; // && nebo || rekurzivně kontrolujeme jinde
        }

        if ($condition instanceof BooleanNot) {
            return []; // ! je ošetřen jiným pravidlem
        }

        // Kontrola typu výrazu
        $conditionType = $scope->getType($condition);

        // Pokud je typ boolean, musí být explicitně porovnán
        if ($conditionType instanceof BooleanType) {
            return [
                RuleErrorBuilder::message(
                    'Boolean value must be compared explicitly using === true or === false.'
                )->build(),
            ];
        }

        // Pokud je union type obsahující boolean
        if ($conditionType instanceof UnionType) {
            foreach ($conditionType->getTypes() as $type) {
                if ($type instanceof BooleanType) {
                    return [
                        RuleErrorBuilder::message(
                            'Boolean value must be compared explicitly using === true or === false.'
                        )->build(),
                    ];
                }
            }
        }

        return [];
    }
}
