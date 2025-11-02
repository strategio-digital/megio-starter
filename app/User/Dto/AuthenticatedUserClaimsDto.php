<?php
declare(strict_types=1);

namespace App\User\Dto;

use App\User\Database\Entity\User;
use Megio\Database\Entity\Auth\Token;

readonly class AuthenticatedUserClaimsDto
{
    /**
     * @param array<non-empty-string, mixed> $claims
     */
    public function __construct(
        public Token $token,
        public User $user,
        public array $claims,
    ) {}
}
