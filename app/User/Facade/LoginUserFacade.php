<?php
declare(strict_types=1);

namespace App\User\Facade;

use App\EntityManager;
use App\User\Dto\AuthenticatedUserClaimsDto;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Http\Request\Dto\UserLoginDto;
use App\User\Resolver\AuthTokenResolver;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Nette\Security\Passwords;

use const PASSWORD_ARGON2ID;

final readonly class LoginUserFacade
{
    public function __construct(
        private EntityManager $em,
        private AuthTokenResolver $authTokenResolver,
    ) {}

    /**
     * @throws UserAuthFacadeException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function execute(UserLoginDto $dto): AuthenticatedUserClaimsDto
    {
        $user = $this->em->getUserRepo()->findOneBy(['email' => $dto->email]);

        if ($user === null || $user->isSoftDeleted() === true) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.login.invalid_credentials_1',
            );
        }

        if ($user->isActive() === false) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.login.invalid_credentials_2',
            );
        }

        if (new Passwords(PASSWORD_ARGON2ID)->verify($dto->password, $user->getPassword()) === false) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.login.invalid_credentials_3',
            );
        }

        $authToken = $this->authTokenResolver->createAuthToken($user);

        $user->setLastLogin(new DateTime());

        $this->em->flush();

        return new AuthenticatedUserClaimsDto($authToken->token, $user, $authToken->claims);
    }
}
