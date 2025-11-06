<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\UserAuthFacade;
use App\User\Http\Request\Dto\UserForgotPasswordDto;
use Doctrine\ORM\Exception\ORMException;
use Megio\Http\Request\AbstractRequest;
use Megio\Http\Serializer\RequestSerializerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserForgotPasswordAbstractRequest extends AbstractRequest
{
    public function __construct(
        private readonly UserAuthFacade $userAuthFacade,
    ) {}

    /**
     * @throws ORMException
     * @throws RequestSerializerException
     */
    public function process(Request $request): Response
    {
        $requestDto = $this->requestToDto(UserForgotPasswordDto::class);

        try {
            $this->userAuthFacade->forgotPassword($requestDto);
        } catch (UserAuthFacadeException) {
            // To prevent user enumeration, we do not disclose whether the email exists.
        }

        return $this->json();
    }
}
