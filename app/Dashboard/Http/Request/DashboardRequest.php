<?php
declare(strict_types=1);

namespace App\Dashboard\Http\Request;

use App\App\Serializer\RequestSerializer;
use App\Dashboard\Facade\DashboardFacade;
use App\User\Database\Entity\User;
use Megio\Http\Request\Request;
use Megio\Security\Auth\AuthUser;
use Symfony\Component\HttpFoundation\Response;

class DashboardRequest extends Request
{
    public function __construct(
        private readonly AuthUser $authUser,
        private readonly RequestSerializer $requestSerializer,
        private readonly DashboardFacade $dashboardFacade,
    ) {}

    public function schema(array $data): array
    {
        return [];
    }

    public function process(array $data): Response
    {
        $user = $this->authUser->get();

        if ($user instanceof User === false) {
            return $this->error(['Uživatel není přihlášen'], Response::HTTP_UNAUTHORIZED);
        }

        $responseDto = $this->dashboardFacade->createDashboardResponse($user);

        return $this->requestSerializer->serialize($responseDto);
    }
}
