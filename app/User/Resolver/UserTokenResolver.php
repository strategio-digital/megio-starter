<?php
declare(strict_types=1);

namespace App\User\Resolver;

use App\User\Database\Entity\User;
use DateTimeImmutable;
use Megio\Security\JWT\JWTResolver;

use function is_string;

final readonly class UserTokenResolver
{
    public function __construct(
        private JWTResolver $jwtResolver,
    ) {}

    public function generateUserToken(
        User $user,
        DateTimeImmutable $expirationAt,
    ): string {
        return $this->jwtResolver->createToken(
            expirationAt: $expirationAt,
            claims: [
                'user_id' => $user->getId(),
            ],
        );
    }

    public function resolveUserIdFromToken(string $token): ?string
    {
        if ($token === '') {
            return null;
        }

        if ($this->jwtResolver->isTrustedToken($token) === false) {
            return null;
        }

        $parsedToken = $this->jwtResolver->parseToken($token);
        $userId = $parsedToken->claims()->get('user_id');

        if (is_string($userId) === false) {
            return null;
        }

        return $userId;
    }
}
