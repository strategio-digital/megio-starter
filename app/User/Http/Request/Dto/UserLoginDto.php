<?php
declare(strict_types=1);

namespace App\User\Http\Request\Dto;

use App\App\Serializer\Dto\RequestDtoInterface;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UserLoginDto implements RequestDtoInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
        public string $password,
    ) {}
}
