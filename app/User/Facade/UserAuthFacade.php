<?php
declare(strict_types=1);

namespace App\User\Facade;

use App\App\EnvReader\EnvConvertor;
use App\EntityManager;
use App\QueueWorker;
use App\User\Database\Entity\User;
use App\User\Dto\AuthenticatedUserClaimsDto;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Http\Request\Dto\UserActivateDto;
use App\User\Http\Request\Dto\UserLoginDto;
use App\User\Http\Request\Dto\UserRegisterDto;
use App\User\Resolver\UserTokenResolver;
use DateMalformedStringException;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Megio\Database\Entity\Auth\Token;
use Megio\Database\Entity\EntityException;
use Megio\Security\JWT\ClaimsFormatter;
use Megio\Security\JWT\JWTResolver;
use Nette\Security\Passwords;

use const PASSWORD_ARGON2ID;

final readonly class UserAuthFacade
{
    public const string USER_ROLE_NAME = 'user';

    public function __construct(
        private EntityManager $em,
        private UserTokenResolver $userTokenResolver,
        private JWTResolver $jwtResolver,
        private ClaimsFormatter $claimsFormatter,
    ) {}

    /**
     * @throws EntityException
     * @throws UniqueConstraintViolationException
     * @throws ORMException
     * @throws Exception
     */
    public function registerUser(
        UserRegisterDto $userRegisterDto,
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

        $token = $this->userTokenResolver->generateUserToken($user);
        $user->setActivationToken($token);
        $this->em->flush();

        $this->em->getQueueRepo()->add(
            worker: QueueWorker::USER_REGISTRATION_MAIL_WORKER,
            payload: ['user_id' => $user->getId()],
        );

        return $user;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws UserAuthFacadeException
     */
    public function activateUser(UserActivateDto $dto): User
    {
        $userId = $this->userTokenResolver->resolveUserIdFromToken($dto->token);

        if ($userId === null) {
            throw new UserAuthFacadeException('user.activation.invalid.token');
        }

        $user = $this->em->getUserRepo()->find($userId);

        if ($user === null) {
            throw new UserAuthFacadeException('user.not.found');
        }

        if ($user->isSoftDeleted() === true) {
            throw new UserAuthFacadeException('user.not.found');
        }

        if ($user->getActivationToken() !== $dto->token) {
            throw new UserAuthFacadeException('user.activation.invalid.token');
        }

        $user->setIsActive(true);
        $user->setActivationToken(null);
        $this->em->flush();

        return $user;
    }

    /**
     * @throws ORMException
     * @throws DateMalformedStringException
     * @throws UserAuthFacadeException
     */
    public function loginUser(UserLoginDto $dto): AuthenticatedUserClaimsDto
    {
        $user = $this->em->getUserRepo()->findOneBy(['email' => $dto->email]);

        if ($user === null || $user->isSoftDeleted() === true) {
            throw new UserAuthFacadeException('user.login.invalid-credentials');
        }

        if ($user->isActive() === false) {
            throw new UserAuthFacadeException('user.login.inactive-account');
        }

        if (new Passwords(PASSWORD_ARGON2ID)->verify($dto->password, $user->getPassword()) === false) {
            throw new UserAuthFacadeException('user.login.invalid-credentials');
        }

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
        $user->setLastLogin(new DateTime());

        $this->em->flush();

        return new AuthenticatedUserClaimsDto($token, $user, $claims);
    }
}
