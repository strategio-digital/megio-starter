<?php
declare(strict_types=1);

namespace App\User\Facade;

use App\EntityManager;
use App\QueueWorker;
use App\User\Database\Entity\User;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Http\Request\Dto\UserRegisterDto;
use App\User\Resolver\UserTokenResolver;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Megio\Database\Entity\EntityException;
use Megio\Translation\Translator;

final readonly class RegisterUserFacade
{
    public const string USER_ROLE_NAME = 'user';

    private const string ACTIVATION_TOKEN_EXPIRATION = '+1 day';

    public function __construct(
        private EntityManager $em,
        private UserTokenResolver $userTokenResolver,
        private Translator $translator,
    ) {}

    /**
     * @throws EntityException
     * @throws UserAuthFacadeException
     * @throws Exception
     * @throws ORMException
     */
    public function execute(UserRegisterDto $dto): User
    {
        $roleName = self::USER_ROLE_NAME;
        $role = $this->em->getAuthRoleRepo()->findOneBy(['name' => $roleName]);

        if ($role === null) {
            throw new Exception('Role not found: ' . $roleName);
        }

        $user = new User();
        $user->setEmail($dto->email);
        $user->setPassword($dto->password);
        $user->addRole($role);

        try {
            $this->em->persist($user);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new UserAuthFacadeException(
                translationKey: 'user.error.registration.email_exists',
                previous: $e,
            );
        }

        $expirationAt = new DateTimeImmutable(self::ACTIVATION_TOKEN_EXPIRATION);
        $token = $this->userTokenResolver->generateUserToken($user, $expirationAt);

        $user->setActivationToken($token);
        $this->em->flush();

        $this->em->getQueueRepo()->add(
            worker: QueueWorker::USER_REGISTRATION_MAIL_WORKER,
            payload: [
                'user_id' => $user->getId(),
                'posix' => $this->translator->getPosix(),
            ],
        );

        return $user;
    }
}
