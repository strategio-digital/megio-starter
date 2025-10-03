<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer\Fixtures\Nested;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TestNestedDto
{
    public function __construct(
        #[Assert\Type('string')]
        public string $name,
        #[Assert\Type(TestNestedUserDto::class)]
        public TestNestedUserDto $sharedWith,
    ) {}
}
