<?php
declare(strict_types=1);

namespace App\User\Facade;

use App\EntityManager;
use App\User\Database\Entity\User;
use App\User\Http\Request\Dto\UserRegisterDto;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Megio\Database\Entity\EntityException;

readonly class UserFacade
{
    public const string USER_ROLE_NAME = 'user';

    public function __construct(
        private EntityManager $em,
    ) {}

    /**
     * @throws EntityException
     * @throws UniqueConstraintViolationException
     */
    public function registerUser(UserRegisterDto $dto): User
    {
        $roleName = self::USER_ROLE_NAME;
        $role = $this->em->getAuthRoleRepo()->findOneBy(['name' => $roleName]);

        if ($role === null) {
            throw new Exception("Role '{$roleName}' not found.");
        }

        $user = new User();
        $user->setEmail($dto->email);
        $user->setPassword($dto->password);
        $user->addRole($role);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
