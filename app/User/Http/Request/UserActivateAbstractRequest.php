<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\UserAuthFacade;
use App\User\Http\Request\Dto\UserActivateDto;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Megio\Http\Request\AbstractRequest;
use Megio\Http\Serializer\RequestSerializerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserActivateAbstractRequest extends AbstractRequest
{
    public function __construct(
        private readonly UserAuthFacade $userAuthFacade,
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
            $this->userAuthFacade->activateUser($requestDto);
        } catch (UserAuthFacadeException $e) {
            return $this->error(['general' => $e->getMessage()]);
        }

        return $this->json(['message' => 'user.activation.success']);
    }
}
