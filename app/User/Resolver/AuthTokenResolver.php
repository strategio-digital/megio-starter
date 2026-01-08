<?php
declare(strict_types=1);

namespace App\User\Resolver;

use App\EntityManager;
use App\User\Database\Entity\User;
use App\User\Dto\AuthTokenDto;
use DateTime;
use DateTimeImmutable;
use Megio\Database\Entity\Auth\Token;
use Megio\Helper\EnvConvertor;
use Megio\Security\JWT\ClaimsFormatter;
use Megio\Security\JWT\JWTResolver;

use function is_string;

final readonly class AuthTokenResolver
{
    public function __construct(
        private EntityManager $em,
        private JWTResolver $jwtResolver,
        private ClaimsFormatter $claimsFormatter,
    ) {}

    /**
     * Creates a new auth token for the user.
     * Note: Does NOT flush - caller is responsible for flushing.
     */
    public function createAuthToken(User $user): AuthTokenDto
    {
        $token = new Token();
        $token->setSource(User::TABLE_NAME);
        $token->setSourceId($user->getId());
        $this->em->persist($token);

        $time = EnvConvertor::toString($_ENV['AUTH_EXPIRATION']);
        $expiration = new DateTime()->modify('+' . $time);
        $immutable = DateTimeImmutable::createFromMutable($expiration);

        $claims = $this->claimsFormatter->format($user, $token);
        $jwt = $this->jwtResolver->createToken($immutable, $claims);

        $token->setExpiration($expiration);
        $token->setToken($jwt);

        return new AuthTokenDto($token, $claims);
    }

    /**
     * Extracts bearer_token_id from a JWT bearer token.
     * Returns null if token is empty or invalid.
     */
    public function extractTokenId(string $bearerToken): ?string
    {
        if ($bearerToken === '') {
            return null;
        }

        if ($this->jwtResolver->isTrustedToken($bearerToken) === false) {
            return null;
        }

        $parsedToken = $this->jwtResolver->parseToken($bearerToken);
        $tokenId = $parsedToken->claims()->get('bearer_token_id');

        if (is_string($tokenId) === false) {
            return null;
        }

        return $tokenId;
    }

    /**
     * Finds a Token entity by bearer token string.
     * Returns null if token is invalid or not found in database.
     */
    public function findTokenByBearer(string $bearerToken): ?Token
    {
        $tokenId = $this->extractTokenId($bearerToken);

        if ($tokenId === null) {
            return null;
        }

        return $this->em->getAuthTokenRepo()->findOneBy(['id' => $tokenId]);
    }
}
