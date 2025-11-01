<?php
declare(strict_types=1);

namespace App\Dashboard\Http\Request;

use App\App\Serializer\RequestSerializer;
use App\Dashboard\Facade\DashboardFacade;
use App\Dashboard\Http\Request\Dto\DashboardResponseDto;
use App\User\Database\Entity\User;
use Megio\Http\Request\Request;
use Megio\Security\Auth\AuthUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

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

    /**
     * @throws ExceptionInterface
     */
    public function process(array $data): Response
    {
        $user = $this->authUser->get();

        if ($user instanceof User === false) {
            return $this->error(['Uživatel není přihlášen'], Response::HTTP_UNAUTHORIZED);
        }

        $this->dashboardFacade->computeSomething();
        $response = DashboardResponseDto::create($user);

        return $this->requestSerializer->serialize($response);
    }
}
