<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer\Fixtures\Simple;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TestNestedUserDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $name,
        #[Assert\Type(TestAddressDto::class)]
        public TestAddressDto $address,
    ) {}
}
