<?php
declare(strict_types=1);

namespace App\Dashboard\Http\Request\Dto;

use App\App\Serializer\Dto\RequestDtoInterface;

readonly class DashboardUserDto implements RequestDtoInterface
{
    public function __construct(
        public string $id,
        public string $email,
        public ?string $lastLogin,
    ) {}
}
