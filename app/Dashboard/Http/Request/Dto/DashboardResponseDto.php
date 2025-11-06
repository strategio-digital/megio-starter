<?php
declare(strict_types=1);

namespace App\Dashboard\Http\Request\Dto;

use App\User\Database\Entity\User;
use Megio\Http\Serializer\Dto\ResponseDtoInterface;

readonly class DashboardResponseDto implements ResponseDtoInterface
{
    public function __construct(
        public DashboardUserDto $user,
    ) {}

    public static function create(User $user): self
    {
        $userDto = new DashboardUserDto(
            id: $user->getId(),
            email: $user->getEmail(),
            lastLogin: $user->getLastLogin()?->format('j. n. Y: H:i'),
        );

        return new self($userDto);
    }
}
