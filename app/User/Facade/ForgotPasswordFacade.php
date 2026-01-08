<?php
declare(strict_types=1);

namespace App\User\Facade;

use App\EntityManager;
use App\QueueWorker;
use App\User\Database\Entity\User;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Http\Request\Dto\UserForgotPasswordDto;
use App\User\Resolver\UserTokenResolver;
use DateTimeImmutable;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Megio\Translation\Translator;

final readonly class ForgotPasswordFacade
{
    private const string RESET_PASSWORD_TOKEN_EXPIRATION = '+1 hour';

    public function __construct(
        private EntityManager $em,
        private UserTokenResolver $userTokenResolver,
        private Translator $translator,
    ) {}

    /**
     * @throws UserAuthFacadeException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function execute(UserForgotPasswordDto $dto): User
    {
        $user = $this->em->getUserRepo()->findOneBy(['email' => $dto->email]);

        if ($user === null || $user->isSoftDeleted() === true) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.forgot_password.invalid_credentials_1',
            );
        }

        if ($user->isActive() === false) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.forgot_password.invalid_credentials_2',
            );
        }

        $expirationAt = new DateTimeImmutable(self::RESET_PASSWORD_TOKEN_EXPIRATION);
        $token = $this->userTokenResolver->generateUserToken($user, $expirationAt);

        $user->setResetPasswordToken($token);
        $this->em->flush();

        $this->em->getQueueRepo()->add(
            worker: QueueWorker::USER_PASSWORD_RESET_MAIL_WORKER,
            payload: [
                'user_id' => $user->getId(),
                'posix' => $this->translator->getPosix(),
            ],
        );

        return $user;
    }
}
