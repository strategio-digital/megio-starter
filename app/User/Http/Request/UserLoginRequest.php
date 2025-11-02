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
use Megio\Http\Request\Request;
use Symfony\Component\HttpFoundation\Response;

class UserLoginRequest extends Request
{
    public function __construct(
        private readonly UserAuthFacade $userFacade,
        private readonly RequestSerializer $requestSerializer,
    ) {}

    public function schema(array $data): array
    {
        return [];
    }

    /**
     * @throws ORMException
     * @throws DateMalformedStringException
     */
    public function process(array $data): Response
    {
        try {
            $requestDto = $this->requestSerializer->denormalize(UserLoginDto::class, $data);
            $authResult = $this->userFacade->loginUser($requestDto);
        } catch (RequestSerializerException $e) {
            return $this->error($e->getErrors());
        } catch (UserAuthFacadeException $e) {
            return $this->error(['general' => $e->getMessage()]);
        }

        return $this->json([
            'bearer_token' => $authResult->token,
            ...$authResult->claims,
        ]);
    }
}
