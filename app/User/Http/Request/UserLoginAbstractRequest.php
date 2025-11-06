<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\App\Serializer\RequestSerializer;
use App\App\Serializer\RequestSerializerException;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\UserAuthFacade;
use App\User\Http\Request\Dto\UserLoginDto;
use DateMalformedStringException;
use Doctrine\ORM\Exception\ORMException;
use Megio\Http\Request\AbstractRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserLoginAbstractRequest extends AbstractRequest
{
    public function __construct(
        private readonly UserAuthFacade $userFacade,
        private readonly RequestSerializer $requestSerializer,
    ) {}

    /**
     * @throws ORMException
     * @throws DateMalformedStringException
     */
    public function process(Request $request): Response
    {
        try {
            $requestDto = $this->requestSerializer->denormalize(
                class: UserLoginDto::class,
                json: $request->getContent(),
            );
            $authResult = $this->userFacade->loginUser($requestDto);
        } catch (RequestSerializerException $e) {
            return $this->error($e->getErrors());
        } catch (UserAuthFacadeException $e) {
            return $this->error(['general' => $e->getMessage()], 403);
        }

        return $this->json([
            'bearer_token' => $authResult->token->getToken(),
            ...$authResult->claims,
        ]);
    }
}
