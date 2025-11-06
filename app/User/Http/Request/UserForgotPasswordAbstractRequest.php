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
use Megio\Http\Request\AbstractRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserForgotPasswordAbstractRequest extends AbstractRequest
{
    public function __construct(
        private readonly UserAuthFacade $userAuthFacade,
        private readonly RequestSerializer $requestSerializer,
    ) {}

    /**
     * @throws ORMException
     * @throws Exception
     */
    public function process(Request $request): Response
    {
        try {
            $requestDto = $this->requestSerializer->denormalize(
                class: UserForgotPasswordDto::class,
                json: $request->getContent(),
            );
            $this->userAuthFacade->forgotPassword($requestDto);
        } catch (RequestSerializerException $e) {
            return $this->error($e->getErrors());
        } catch (UserAuthFacadeException) {
            // To prevent user enumeration, we do not disclose whether the email exists.
        }

        return $this->json();
    }
}
