<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\App\Serializer\RequestSerializer;
use App\App\Serializer\RequestSerializerException;
use App\User\Facade\UserFacade;
use App\User\Http\Request\Dto\UserRegisterDto;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Megio\Database\Entity\EntityException;
use Megio\Http\Request\Request;
use Symfony\Component\HttpFoundation\Response;

class UserRegisterRequest extends Request
{
    public function __construct(
        private readonly UserFacade $userFacade,
        private readonly RequestSerializer $requestSerializer,
    ) {}

    public function schema(array $data): array
    {
        return [];
    }

    public function process(array $data): Response
    {
        try {
            $requestDto = $this->requestSerializer->denormalize(UserRegisterDto::class, $data);
        } catch (RequestSerializerException $e) {
            return $this->error($e->getErrors());
        }

        try {
            $this->userFacade->registerUser($requestDto);
        } catch (EntityException $e) {
            return $this->error(['general' => $e->getMessage()]);
        } catch (UniqueConstraintViolationException) {
            return $this->error(['email' => 'User with this email already exists.']);
        }

        return $this->json();
    }
}
