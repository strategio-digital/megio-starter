<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer\Fixtures\Simple;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TestAddressDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $street,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $city,
        #[Assert\Type('string')]
        #[Assert\Length(min: 5, max: 10)]
        public string $zipCode,
    ) {}
}
