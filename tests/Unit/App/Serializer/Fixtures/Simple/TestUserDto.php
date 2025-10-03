<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer\Fixtures\Simple;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TestUserDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\Type('int')]
        #[Assert\Range(min: 18, max: 120)]
        public int $age,
    ) {}
}
