<?php declare(strict_types=1);

namespace Tests\Feature\User;

use App\EntityManager;
use App\User\Database\Entity\User;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\RegisterUserFacade;
use App\User\Http\Request\Dto\UserRegisterDto;
use App\User\Resolver\AuthTokenResolver;
use Megio\Database\Entity\EntityException;
use Megio\Helper\EnvConvertor;
use Tests\TestCase;

class AuthTokenResolverTest extends TestCase
{
    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     */
    public function testCreateAuthTokenReturnsValidToken(): void
    {
        $user = $this->createUser();

        $authToken = $this->getService(AuthTokenResolver::class)->createAuthToken($user);

        $this->assertNotEmpty($authToken->token->getId());
        $this->assertNotEmpty($authToken->token->getToken());
        $this->assertSame(User::TABLE_NAME, $authToken->token->getSource());
        $this->assertSame($user->getId(), $authToken->token->getSourceId());
        $this->assertArrayHasKey('bearer_token_id', $authToken->claims);
        $this->assertArrayHasKey('user', $authToken->claims);
        $this->assertSame($authToken->token->getId(), $authToken->claims['bearer_token_id']);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     */
    public function testCreateAuthTokenClaimsContainUserData(): void
    {
        $user = $this->createUser();

        $authToken = $this->getService(AuthTokenResolver::class)->createAuthToken($user);

        $this->assertArrayHasKey('user', $authToken->claims);
        $this->assertIsArray($authToken->claims['user']);

        /** @var array<string, mixed> $userClaims */
        $userClaims = $authToken->claims['user'];
        $this->assertSame($user->getId(), $userClaims['id']);
        $this->assertSame($user->getEmail(), $userClaims['email']);
        $this->assertArrayHasKey('roles', $userClaims);
        $this->assertArrayHasKey('resources', $userClaims);
    }

    public function testExtractTokenIdReturnsNullForEmptyToken(): void
    {
        $tokenId = $this->getService(AuthTokenResolver::class)->extractTokenId('');

        $this->assertNull($tokenId);
    }

    public function testExtractTokenIdReturnsNullForInvalidToken(): void
    {
        $tokenId = $this->getService(AuthTokenResolver::class)->extractTokenId('invalid-token');

        $this->assertNull($tokenId);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     */
    public function testExtractTokenIdReturnsIdForValidToken(): void
    {
        $user = $this->createUser();
        $authToken = $this->getService(AuthTokenResolver::class)->createAuthToken($user);

        $tokenId = $this->getService(AuthTokenResolver::class)->extractTokenId($authToken->token->getToken());

        $this->assertSame($authToken->token->getId(), $tokenId);
    }

    public function testFindTokenByBearerReturnsNullForEmptyToken(): void
    {
        $token = $this->getService(AuthTokenResolver::class)->findTokenByBearer('');

        $this->assertNull($token);
    }

    public function testFindTokenByBearerReturnsNullForInvalidToken(): void
    {
        $token = $this->getService(AuthTokenResolver::class)->findTokenByBearer('invalid-token');

        $this->assertNull($token);
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     */
    public function testFindTokenByBearerReturnsTokenWhenExists(): void
    {
        $user = $this->createUser();
        $resolver = $this->getService(AuthTokenResolver::class);

        $authToken = $resolver->createAuthToken($user);

        // Flush to persist token to database
        $this->getService(EntityManager::class)->flush();

        $foundToken = $resolver->findTokenByBearer($authToken->token->getToken());

        $this->assertNotNull($foundToken);
        $this->assertSame($authToken->token->getId(), $foundToken->getId());
    }

    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     */
    private function createUser(): User
    {
        $email = EnvConvertor::toString($_ENV['APP_DEVELOPER_MAIL']);

        $dto = new UserRegisterDto(
            email: $email,
            password: 'SecurePass123!',
        );

        $user = $this->getService(RegisterUserFacade::class)->execute($dto);
        $user->setIsActive(true);

        return $user;
    }
}
