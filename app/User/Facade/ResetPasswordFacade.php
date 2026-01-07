<?php
declare(strict_types=1);

namespace App\User\Facade;

use App\EntityManager;
use App\User\Database\Entity\User;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Http\Request\Dto\UserResetPasswordDto;
use App\User\Resolver\UserTokenResolver;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Megio\Database\Entity\EntityException;

final readonly class ResetPasswordFacade
{
    public function __construct(
        private EntityManager $em,
        private UserTokenResolver $userTokenResolver,
    ) {}

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function execute(UserResetPasswordDto $dto): User
    {
        $userId = $this->userTokenResolver->extractUserIdFromToken($dto->token);

        if ($userId === null) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.reset_password.invalid_token_1',
            );
        }

        $user = $this->em->getUserRepo()->find($userId);

        if ($user === null) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.reset_password.invalid_token_2',
            );
        }

        if ($user->isSoftDeleted() === true) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.reset_password.invalid_token_3',
            );
        }

        if ($user->isActive() === false) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.reset_password.invalid_token_4',
            );
        }

        if ($user->getResetPasswordToken() !== $dto->token) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.reset_password.invalid_token_5',
            );
        }

        $user->setPassword($dto->password);
        $user->setResetPasswordToken(null);
        $this->em->flush();

        return $user;
    }
}
