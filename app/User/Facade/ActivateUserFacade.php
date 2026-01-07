<?php
declare(strict_types=1);

namespace App\User\Facade;

use App\EntityManager;
use App\User\Database\Entity\User;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Http\Request\Dto\UserActivateDto;
use App\User\Resolver\UserTokenResolver;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final readonly class ActivateUserFacade
{
    public function __construct(
        private EntityManager $em,
        private UserTokenResolver $userTokenResolver,
    ) {}

    /**
     * @throws OptimisticLockException
     * @throws UserAuthFacadeException
     * @throws ORMException
     */
    public function execute(UserActivateDto $dto): User
    {
        $userId = $this->userTokenResolver->extractUserIdFromToken($dto->token);

        if ($userId === null) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.activation.invalid_token_1',
            );
        }

        $user = $this->em->getUserRepo()->find($userId);

        if ($user === null) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.activation.invalid_token_2',
            );
        }

        if ($user->isSoftDeleted() === true) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.activation.invalid_token_3',
            );
        }

        if ($user->getActivationToken() !== $dto->token) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.activation.invalid_token_4',
            );
        }

        $user->setIsActive(true);
        $user->setActivationToken(null);
        $this->em->flush();

        return $user;
    }
}
