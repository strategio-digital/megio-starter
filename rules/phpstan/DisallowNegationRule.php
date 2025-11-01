<?php

declare(strict_types=1);

namespace App\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<BooleanNot>
 */
class DisallowNegationRule implements Rule
{
    public function getNodeType(): string
    {
        return BooleanNot::class;
    }

    public function processNode(
        Node $node,
        Scope $scope,
    ): array {
        return [
            RuleErrorBuilder::message(
                'Usage of negation operator (!) is not allowed. Use explicit comparison instead (e.g., === false, === null, === 0).',
            )->identifier('custom.disallowNegation')->build(),
        ];
    }
}
