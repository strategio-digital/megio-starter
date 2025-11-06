<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\UserAuthFacade;
use App\User\Http\Request\Dto\UserLoginDto;
use DateMalformedStringException;
use Doctrine\ORM\Exception\ORMException;
use Megio\Http\Request\AbstractRequest;
use Megio\Http\Serializer\RequestSerializerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserLoginAbstractRequest extends AbstractRequest
{
    public function __construct(
        private readonly UserAuthFacade $userFacade,
    ) {}

    /**
     * @throws ORMException
     * @throws DateMalformedStringException
     * @throws RequestSerializerException
     */
    public function process(Request $request): Response
    {
        $requestDto = $this->requestToDto(UserLoginDto::class);

        try {
            $authResult = $this->userFacade->loginUser($requestDto);
        } catch (UserAuthFacadeException $e) {
            return $this->error(['general' => $e->getMessage()], 403);
        }

        return $this->json([
            'bearer_token' => $authResult->token->getToken(),
            ...$authResult->claims,
        ]);
    }
}
