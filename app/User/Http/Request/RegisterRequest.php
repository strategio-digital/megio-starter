<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\RegisterUserFacade;
use App\User\Http\Request\Dto\UserRegisterDto;
use Doctrine\ORM\Exception\ORMException;
use Megio\Database\Entity\EntityException;
use Megio\Http\Request\AbstractRequest;
use Megio\Http\Serializer\RequestSerializerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterRequest extends AbstractRequest
{
    public function __construct(
        private readonly RegisterUserFacade $registerUserFacade,
    ) {}

    /**
     * @throws ORMException
     * @throws EntityException
     * @throws RequestSerializerException
     */
    public function process(Request $request): Response
    {
        $requestDto = $this->requestToDto(UserRegisterDto::class);

        try {
            $this->registerUserFacade->execute($requestDto);
        } catch (UserAuthFacadeException $e) {
            return $this->error([
                'general' => $e->getTranslationKey(),
                'params' => $e->getTranslationParams(),
            ]);
        }

        return $this->json();
    }
}
