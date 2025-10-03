<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer\Fixtures\Complex;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TestAuthorDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $firstName,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $lastName,
        #[Assert\Email]
        public string $email,
    ) {}
}
