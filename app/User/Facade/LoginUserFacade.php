<?php
declare(strict_types=1);

namespace App\User\Facade;

use App\EntityManager;
use App\User\Database\Entity\User;
use App\User\Dto\AuthenticatedUserClaimsDto;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Http\Request\Dto\UserLoginDto;
use DateMalformedStringException;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Megio\Database\Entity\Auth\Token;
use Megio\Helper\EnvConvertor;
use Megio\Security\JWT\ClaimsFormatter;
use Megio\Security\JWT\JWTResolver;
use Nette\Security\Passwords;

use const PASSWORD_ARGON2ID;

final readonly class LoginUserFacade
{
    public function __construct(
        private EntityManager $em,
        private JWTResolver $jwtResolver,
        private ClaimsFormatter $claimsFormatter,
    ) {}

    /**
     * @throws UserAuthFacadeException
     * @throws DateMalformedStringException
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
