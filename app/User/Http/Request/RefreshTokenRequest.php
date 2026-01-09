<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\User\Database\Entity\User;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\RefreshTokenFacade;
use DateMalformedStringException;
use Doctrine\ORM\Exception\ORMException;
use Megio\Http\Request\AbstractRequest;
use Megio\Security\Auth\AuthUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function is_string;
use function str_replace;
use function trim;

class RefreshTokenRequest extends AbstractRequest
{
    public function __construct(
        private readonly RefreshTokenFacade $refreshTokenFacade,
        private readonly AuthUser $authUser,
    ) {}

    /**
     * @throws ORMException
     * @throws DateMalformedStringException
     */
    public function process(Request $request): Response
    {
        $authHeader = $request->headers->get('Authorization');
        if (is_string($authHeader) === false) {
            return $this->error(['general' => 'auth.missing_authorization_header'], 401);
        }

        $bearerToken = trim(str_replace('Bearer', '', $authHeader));

        // Get current user (set by AuthRequest subscriber)
        $user = $this->authUser->get();
        if (($user instanceof User) === false) {
            return $this->error(['general' => 'auth.user_not_authenticated'], 401);
        }

        try {
            $authResult = $this->refreshTokenFacade->execute($bearerToken, $user);
        } catch (UserAuthFacadeException $e) {
            return $this->error([
                'general' => $e->getTranslationKey(),
                'params' => $e->getTranslationParams(),
            ], 401);
        }

        return $this->json([
            'bearer_token' => $authResult->token->getToken(),
            ...$authResult->claims,
        ]);
    }
}
