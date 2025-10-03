<?php
declare(strict_types=1);

namespace App\Dashboard\Facade;

use App\Dashboard\Http\Request\Dto\DashboardResponseDto;
use App\User\Database\Entity\User;

readonly class DashboardFacade
{
    public function createDashboardResponse(User $user): DashboardResponseDto
    {
        return DashboardResponseDto::create(
            user: $user,
        );
    }
}
