<?php
declare(strict_types=1);

namespace App\User\Dto;

use Megio\Database\Entity\Auth\Token;

final readonly class AuthTokenDto
{
    /**
     * @param array<non-empty-string, mixed> $claims
     */
    public function __construct(
        public Token $token,
        public array $claims,
    ) {}
}
