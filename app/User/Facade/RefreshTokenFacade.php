<?php
declare(strict_types=1);

namespace App\User\Facade;

use App\EntityManager;
use App\User\Database\Entity\User;
use App\User\Dto\AuthenticatedUserClaimsDto;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Resolver\AuthTokenResolver;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final readonly class RefreshTokenFacade
{
    private const int MIN_TOKEN_AGE_SECONDS = 30 * 60; // 30 minutes

    public function __construct(
        private EntityManager $em,
        private AuthTokenResolver $authTokenResolver,
    ) {}

    /**
     * @throws UserAuthFacadeException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function execute(string $bearerToken, User $user): AuthenticatedUserClaimsDto
    {
        $currentToken = $this->authTokenResolver->findTokenByBearer($bearerToken);

        if ($currentToken === null) {
            throw new UserAuthFacadeException(
                translationKey: 'auth.token_not_found',
            );
        }

        if ($currentToken->getSourceId() !== $user->getId()) {
            throw new UserAuthFacadeException(
                translationKey: 'auth.token_user_mismatch',
            );
        }

        // Check token age - must be at least 30 minutes old
        $tokenAge = new DateTime()->getTimestamp() - $currentToken->getCreatedAt()->getTimestamp();
        if (($tokenAge < self::MIN_TOKEN_AGE_SECONDS) === true) {
            throw new UserAuthFacadeException(
                translationKey: 'auth.token_too_young',
            );
        }

        // Delete old token (token rotation)
        $this->em->remove($currentToken);

        $authToken = $this->authTokenResolver->createAuthToken($user);

        $this->em->flush();

        return new AuthenticatedUserClaimsDto($authToken->token, $user, $authToken->claims);
    }
}
