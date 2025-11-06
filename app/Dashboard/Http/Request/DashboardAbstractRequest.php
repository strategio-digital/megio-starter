<?php
declare(strict_types=1);

namespace App\Dashboard\Http\Request;

use App\Dashboard\Facade\DashboardFacade;
use App\Dashboard\Http\Request\Dto\DashboardResponseDto;
use App\User\Database\Entity\User;
use Megio\Http\Request\AbstractRequest;
use Megio\Security\Auth\AuthUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class DashboardAbstractRequest extends AbstractRequest
{
    public function __construct(
        private readonly AuthUser $authUser,
        private readonly DashboardFacade $dashboardFacade,
    ) {}

    /**
     * @throws ExceptionInterface
     */
    public function process(Request $request): Response
    {
        $user = $this->authUser->get();

        if ($user instanceof User === false) {
            return $this->error([
                'general' => 'user.not_authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $this->dashboardFacade->computeSomething();
        $response = DashboardResponseDto::create($user);

        return $this->requestSerializer->serialize($response);
    }
}
