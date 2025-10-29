<?php
declare(strict_types=1);

namespace App\Dashboard\Http\Request\Dto;

readonly class DashboardUserDto
{
    public function __construct(
        public string $id,
        public string $email,
        public ?string $lastLogin,
    ) {}
}
