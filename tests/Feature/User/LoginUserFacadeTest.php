<?php declare(strict_types=1);

namespace Tests\Feature\User;

use App\User\Database\Entity\User;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\LoginUserFacade;
use App\User\Facade\RegisterUserFacade;
use App\User\Http\Request\Dto\UserLoginDto;
use App\User\Http\Request\Dto\UserRegisterDto;
use Doctrine\ORM\Exception\ORMException;
use Megio\Database\Entity\EntityException;
use Megio\Helper\EnvConvertor;
use Tests\TestCase;

class LoginUserFacadeTest extends TestCase
{
    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     */
    public function testLoginSuccessfully(): void
    {
        $credentials = $this->createActiveUser();

        $loginDto = new UserLoginDto(
            email: $credentials['email'],
            password: $credentials['password'],
        );

        $result = $this->getService(LoginUserFacade::class)->execute($loginDto);

        $this->assertSame($credentials['email'], $result->user->getEmail());
        $this->assertNotEmpty($result->token->getToken());
        $this->assertArrayHasKey('bearer_token_id', $result->claims);
        $this->assertArrayHasKey('user', $result->claims);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     */
    public function testLoginUpdatesLastLoginTimestamp(): void
    {
        $credentials = $this->createActiveUser();

        $loginDto = new UserLoginDto(
            email: $credentials['email'],
            password: $credentials['password'],
        );

        $result = $this->getService(LoginUserFacade::class)->execute($loginDto);

        $this->assertNotNull($result->user->getLastLogin());
    }

    /**
     * @throws EntityException
     * @throws ORMException
     */
    public function testLoginThrowsExceptionForNonExistentEmail(): void
    {
        $loginDto = new UserLoginDto(
            email: 'nonexistent@example.com',
            password: 'SomePassword123!',
        );

        $this->expectException(UserAuthFacadeException::class);
        $this->getService(LoginUserFacade::class)->execute($loginDto);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     */
    public function testLoginThrowsExceptionForInactiveUser(): void
    {
        $email = EnvConvertor::toString($_ENV['APP_DEVELOPER_MAIL']);
        $password = 'SecurePass123!';

        $dto = new UserRegisterDto(email: $email, password: $password);
        $this->getService(RegisterUserFacade::class)->execute($dto);
        // User is NOT activated (isActive = false by default)

        $loginDto = new UserLoginDto(email: $email, password: $password);

        $this->expectException(UserAuthFacadeException::class);
        $this->getService(LoginUserFacade::class)->execute($loginDto);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     */
    public function testLoginThrowsExceptionForWrongPassword(): void
    {
        $credentials = $this->createActiveUser();

        $loginDto = new UserLoginDto(
            email: $credentials['email'],
            password: 'WrongPassword123!',
        );

        $this->expectException(UserAuthFacadeException::class);
        $this->getService(LoginUserFacade::class)->execute($loginDto);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     */
    public function testLoginThrowsExceptionForSoftDeletedUser(): void
    {
        $email = EnvConvertor::toString($_ENV['APP_DEVELOPER_MAIL']);
        $password = 'SecurePass123!';

        $dto = new UserRegisterDto(email: $email, password: $password);
        $user = $this->getService(RegisterUserFacade::class)->execute($dto);
        $user->setIsActive(true);
        $user->setIsSoftDeleted(true);

        $loginDto = new UserLoginDto(email: $email, password: $password);

        $this->expectException(UserAuthFacadeException::class);
        $this->getService(LoginUserFacade::class)->execute($loginDto);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     *
     * @return array{email: string, password: string}
     */
    private function createActiveUser(): array
    {
        $email = EnvConvertor::toString($_ENV['APP_DEVELOPER_MAIL']);
        $password = 'SecurePass123!';

        $dto = new UserRegisterDto(email: $email, password: $password);
        $user = $this->getService(RegisterUserFacade::class)->execute($dto);
        $user->setIsActive(true);

        return ['email' => $email, 'password' => $password];
    }
}
