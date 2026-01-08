<?php declare(strict_types=1);

namespace Tests\Feature\User;

use App\EntityManager;
use App\User\Dto\AuthenticatedUserClaimsDto;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\LoginUserFacade;
use App\User\Facade\RefreshTokenFacade;
use App\User\Facade\RegisterUserFacade;
use App\User\Http\Request\Dto\UserLoginDto;
use App\User\Http\Request\Dto\UserRegisterDto;
use App\User\Http\Request\RefreshTokenRequest;
use DateMalformedStringException;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Megio\Database\Entity\Auth\Token;
use Megio\Database\Entity\EntityException;
use Megio\Helper\EnvConvertor;
use Megio\Security\Auth\AuthUser;
use Symfony\Component\HttpFoundation\Request;
use Tests\TestCase;

class RefreshTokenTest extends TestCase
{
    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     * @throws DateMalformedStringException
     * @throws OptimisticLockException
     */
    public function testRefreshesTokenSuccessfully(): void
    {
        // Arrange - Create and login user
        $user = $this->createAndLoginUser();
        $loginResult = $this->loginUser($user['email'], $user['password']);

        $originalBearerToken = $loginResult->token->getToken();
        $originalTokenId = $loginResult->token->getId();

        // Age the token to be older than 30 minutes
        $this->ageToken($loginResult->token, 31);

        // Act - Refresh the token
        $refreshResult = $this->getService(RefreshTokenFacade::class)->execute($originalBearerToken, $loginResult->user);

        // Assert - New token was created
        $this->assertNotSame($originalTokenId, $refreshResult->token->getId());
        $this->assertNotSame($originalBearerToken, $refreshResult->token->getToken());

        // Assert - User data is preserved
        $this->assertSame($loginResult->user->getId(), $refreshResult->user->getId());
        $this->assertSame($loginResult->user->getEmail(), $refreshResult->user->getEmail());

        // Assert - Claims contain required data
        $this->assertArrayHasKey('bearer_token_id', $refreshResult->claims);
        $this->assertArrayHasKey('user', $refreshResult->claims);
        $this->assertSame($refreshResult->token->getId(), $refreshResult->claims['bearer_token_id']);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     * @throws DateMalformedStringException
     * @throws OptimisticLockException
     */
    public function testTokenRotationDeletesOldToken(): void
    {
        // Arrange - Create and login user
        $user = $this->createAndLoginUser();
        $loginResult = $this->loginUser($user['email'], $user['password']);

        $originalBearerToken = $loginResult->token->getToken();

        // Age the token to be older than 30 minutes
        $this->ageToken($loginResult->token, 31);

        // Act - Refresh the token
        $this->getService(RefreshTokenFacade::class)->execute($originalBearerToken, $loginResult->user);

        // Assert - Old token no longer exists in database
        // Trying to refresh with old token should fail
        $this->expectException(UserAuthFacadeException::class);
        $this->getService(RefreshTokenFacade::class)->execute($originalBearerToken, $loginResult->user);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     * @throws DateMalformedStringException
     * @throws OptimisticLockException
     */
    public function testThrowsExceptionForTokenTooYoung(): void
    {
        // Arrange - Create and login user
        $user = $this->createAndLoginUser();
        $loginResult = $this->loginUser($user['email'], $user['password']);

        // Token is just created, less than 30 minutes old

        // Assert & Act
        $this->expectException(UserAuthFacadeException::class);
        $this->getService(RefreshTokenFacade::class)->execute($loginResult->token->getToken(), $loginResult->user);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     * @throws DateMalformedStringException
     * @throws OptimisticLockException
     */
    public function testThrowsExceptionForEmptyBearerToken(): void
    {
        // Arrange - Create and login user
        $user = $this->createAndLoginUser();
        $loginResult = $this->loginUser($user['email'], $user['password']);

        // Assert & Act
        $this->expectException(UserAuthFacadeException::class);
        $this->getService(RefreshTokenFacade::class)->execute('', $loginResult->user);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     * @throws DateMalformedStringException
     * @throws OptimisticLockException
     */
    public function testThrowsExceptionForInvalidToken(): void
    {
        // Arrange - Create and login user
        $user = $this->createAndLoginUser();
        $loginResult = $this->loginUser($user['email'], $user['password']);

        // Assert & Act - Use completely invalid token
        $this->expectException(Exception::class);
        $this->getService(RefreshTokenFacade::class)->execute('invalid-token-string', $loginResult->user);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     * @throws DateMalformedStringException
     * @throws OptimisticLockException
     */
    public function testThrowsExceptionWhenTokenBelongsToDifferentUser(): void
    {
        // Arrange - Create two users
        $user1Data = $this->createAndLoginUser();
        $user1LoginResult = $this->loginUser($user1Data['email'], $user1Data['password']);

        // Create second user with different email
        $user2Email = 'second-' . EnvConvertor::toString($_ENV['APP_DEVELOPER_MAIL']);
        $user2Password = 'SecurePass456!';

        $user2RegisterDto = new UserRegisterDto(
            email: $user2Email,
            password: $user2Password,
        );

        $user2 = $this->getService(RegisterUserFacade::class)->execute($user2RegisterDto);
        $user2->setIsActive(true);

        $user2LoginResult = $this->loginUser($user2Email, $user2Password);

        // Age the token to be older than 30 minutes
        $this->ageToken($user1LoginResult->token, 31);

        // Act & Assert - Try to refresh User1's token with User2's identity
        // This should fail because token belongs to User1, not User2
        $this->expectException(UserAuthFacadeException::class);
        $this->getService(RefreshTokenFacade::class)->execute($user1LoginResult->token->getToken(), $user2LoginResult->user);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     * @throws DateMalformedStringException
     * @throws OptimisticLockException
     */
    public function testThrowsExceptionWhenTokenNotFoundInDatabase(): void
    {
        // Arrange - Create and login user
        $user = $this->createAndLoginUser();
        $loginResult = $this->loginUser($user['email'], $user['password']);

        $bearerToken = $loginResult->token->getToken();

        // Manually delete the token from database
        $em = $this->getService(EntityManager::class);
        $em->remove($loginResult->token);
        $em->flush();

        // Act & Assert - Try to refresh deleted token
        $this->expectException(UserAuthFacadeException::class);
        $this->getService(RefreshTokenFacade::class)->execute($bearerToken, $loginResult->user);
    }

    /**
     * @throws ORMException
     * @throws DateMalformedStringException
     */
    public function testHttpReturns401WhenAuthorizationHeaderMissing(): void
    {
        // Arrange - Create mock request without Authorization header
        $request = new Request();

        // Get RefreshTokenRequest instance
        $refreshTokenRequest = new RefreshTokenRequest(
            $this->getService(RefreshTokenFacade::class),
            $this->getService(AuthUser::class),
        );

        // Act
        $response = $refreshTokenRequest->process($request);

        // Assert
        $this->assertSame(401, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('auth.missing_authorization_header', $content);
    }

    /**
     * @throws ORMException
     * @throws DateMalformedStringException
     */
    public function testHttpReturns401WhenUserNotAuthenticated(): void
    {
        // Arrange - Create mock request with Authorization header but no authenticated user
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer some-token');

        // Get RefreshTokenRequest instance (AuthUser will return null as no user is set)
        $refreshTokenRequest = new RefreshTokenRequest(
            $this->getService(RefreshTokenFacade::class),
            $this->getService(AuthUser::class),
        );

        // Act
        $response = $refreshTokenRequest->process($request);

        // Assert
        $this->assertSame(401, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('auth.user_not_authenticated', $content);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     *
     * @return array{email: string, password: string}
     */
    private function createAndLoginUser(): array
    {
        $developerMail = EnvConvertor::toString($_ENV['APP_DEVELOPER_MAIL']);
        $password = 'SecurePass123!';

        $userRegisterDto = new UserRegisterDto(
            email: $developerMail,
            password: $password,
        );

        $user = $this->getService(RegisterUserFacade::class)->execute($userRegisterDto);

        // Activate user for login
        $user->setIsActive(true);

        return [
            'email' => $developerMail,
            'password' => $password,
        ];
    }

    /**
     * @throws UserAuthFacadeException
     * @throws DateMalformedStringException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function loginUser(string $email, string $password): AuthenticatedUserClaimsDto
    {
        $loginDto = new UserLoginDto(
            email: $email,
            password: $password,
        );

        return $this->getService(LoginUserFacade::class)->execute($loginDto);
    }

    /**
     * Ages the token by setting its createdAt to the specified minutes in the past.
     */
    private function ageToken(Token $token, int $minutes): void
    {
        $pastTime = new DateTime();
        $pastTime->modify("-{$minutes} minutes");
        $token->setCreatedAt($pastTime);
        $this->getService(EntityManager::class)->flush();
    }
}
