<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer\Fixtures\Complex;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TestMetadataDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $title,
        #[Assert\Type('int')]
        #[Assert\Range(min: 1, max: 100)]
        public int $priority,
        #[Assert\Type(TestAuthorDto::class)]
        public TestAuthorDto $author,
    ) {}
}
