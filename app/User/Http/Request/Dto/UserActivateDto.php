<?php
declare(strict_types=1);

namespace App\User\Http\Request\Dto;

use Megio\Http\Serializer\Dto\RequestDtoInterface;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UserActivateDto implements RequestDtoInterface
{
    public function __construct(
        #[Assert\NotBlank]
        public string $token,
    ) {}
}
