<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer\Fixtures\Complex;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TestItemDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $code,
        #[Assert\Type('float')]
        #[Assert\Range(min: 0.01)]
        public float $price,
    ) {}
}
