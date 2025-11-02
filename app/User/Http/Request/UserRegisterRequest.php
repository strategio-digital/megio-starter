<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\App\Serializer\RequestSerializer;
use App\App\Serializer\RequestSerializerException;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\UserAuthFacade;
use App\User\Http\Request\Dto\UserRegisterDto;
use Doctrine\ORM\Exception\ORMException;
use Megio\Database\Entity\EntityException;
use Megio\Http\Request\Request;
use Symfony\Component\HttpFoundation\Response;

class UserRegisterRequest extends Request
{
    public function __construct(
        private readonly UserAuthFacade $userAuthFacade,
        private readonly RequestSerializer $requestSerializer,
    ) {}

    public function schema(array $data): array
    {
        return [];
    }

    /**
     * @throws ORMException
     * @throws EntityException
     */
    public function process(array $data): Response
    {
        try {
            $requestDto = $this->requestSerializer->denormalize(UserRegisterDto::class, $data);
            $this->userAuthFacade->registerUser($requestDto);
        } catch (RequestSerializerException $e) {
            return $this->error($e->getErrors());
        } catch (UserAuthFacadeException $e) {
            return $this->error(['general' => $e->getMessage()]);
        }

        return $this->json();
    }
}
