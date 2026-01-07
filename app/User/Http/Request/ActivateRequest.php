<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\User\Facade\ActivateUserFacade;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Http\Request\Dto\UserActivateDto;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Megio\Http\Request\AbstractRequest;
use Megio\Http\Serializer\RequestSerializerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivateRequest extends AbstractRequest
{
    public function __construct(
        private readonly ActivateUserFacade $activateUserFacade,
    ) {}

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws RequestSerializerException
     */
    public function process(Request $request): Response
    {
        $requestDto = $this->requestToDto(UserActivateDto::class);

        try {
            $this->activateUserFacade->execute($requestDto);
        } catch (UserAuthFacadeException $e) {
            return $this->error([
                'general' => $e->getTranslationKey(),
                'params' => $e->getTranslationParams(),
            ]);
        }

        return $this->json();
    }
}
