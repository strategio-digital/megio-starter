<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\ResetPasswordFacade;
use App\User\Http\Request\Dto\UserResetPasswordDto;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Megio\Database\Entity\EntityException;
use Megio\Http\Request\AbstractRequest;
use Megio\Http\Serializer\RequestSerializerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordRequest extends AbstractRequest
{
    public function __construct(
        private readonly ResetPasswordFacade $resetPasswordFacade,
    ) {}

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityException
     * @throws RequestSerializerException
     */
    public function process(Request $request): Response
    {
        $requestDto = $this->requestToDto(UserResetPasswordDto::class);

        try {
            $this->resetPasswordFacade->execute($requestDto);
        } catch (UserAuthFacadeException $e) {
            return $this->error([
                'general' => $e->getTranslationKey(),
                'params' => $e->getTranslationParams(),
            ]);
        }

        return $this->json();
    }
}
