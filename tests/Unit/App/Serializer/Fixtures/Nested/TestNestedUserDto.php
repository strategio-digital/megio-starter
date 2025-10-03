<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer\Fixtures\Nested;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TestNestedUserDto
{
    public function __construct(
        #[Assert\Type('bool')]
        public bool $accepted,
        #[Assert\Type('int')]
        public int $user_id,
        #[Assert\Type('string')]
        public string $user_name,
    ) {}
}
