<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\App\Serializer\RequestSerializer;
use App\App\Serializer\RequestSerializerException;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\UserAuthFacade;
use App\User\Http\Request\Dto\UserForgotPasswordDto;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Megio\Http\Request\Request;
use Symfony\Component\HttpFoundation\Response;

class UserForgotPasswordRequest extends Request
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
     * @throws Exception
     */
    public function process(array $data): Response
    {
        try {
            $requestDto = $this->requestSerializer->denormalizeFromArray(UserForgotPasswordDto::class, $data);
            $this->userAuthFacade->forgotPassword($requestDto);
        } catch (RequestSerializerException $e) {
            return $this->error($e->getErrors());
        } catch (UserAuthFacadeException) {
            // To prevent user enumeration, we do not disclose whether the email exists.
        }

        return $this->json();
    }
}
