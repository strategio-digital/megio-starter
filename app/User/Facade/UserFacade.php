<?php
declare(strict_types=1);

namespace App\User\Facade;

use App\EntityManager;
use App\User\Database\Entity\User;
use App\User\Facade\Exception\UserFacadeException;
use App\User\Http\Request\Dto\UserActivateDto;
use App\User\Http\Request\Dto\UserRegisterDto;
use App\User\Mail\ActivationMailer;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Megio\Database\Entity\EntityException;

final readonly class UserFacade
{
    public const string USER_ROLE_NAME = 'user';

    public function __construct(
        private EntityManager $em,
        private ActivationMailer $activationMailer,
    ) {}

    /**
     * @throws EntityException
     * @throws UniqueConstraintViolationException
     * @throws ORMException
     * @throws Exception
     */
    public function registerUser(
        UserRegisterDto $userRegisterDto,
        bool $sendMail = true,
    ): User {
        $roleName = self::USER_ROLE_NAME;
        $role = $this->em->getAuthRoleRepo()->findOneBy(['name' => $roleName]);

        if ($role === null) {
            throw new Exception("Role '{$roleName}' not found.");
        }

        $user = new User();
        $user->setEmail($userRegisterDto->email);
        $user->setPassword($userRegisterDto->password);
        $user->addRole($role);

        $this->em->persist($user);
        $this->em->flush();

        if ($sendMail === true) {
            $this->activationMailer->send($user, 'test-token');
        }

        return $user;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws UserFacadeException
     */
    public function activateUser(UserActivateDto $dto): User
    {
        $user = $this->em->getUserRepo()->find($dto->userId);

        if ($user === null) {
            throw new UserFacadeException('user.not.found');
        }

        // TODO: activation token verification logic
        if ($dto->token !== 'test-token') {
            throw new UserFacadeException('user.activation.invalid.token');
        }

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
