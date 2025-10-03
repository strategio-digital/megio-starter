<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer\Fixtures\Complex;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TestComplexNestedDto
{
    /**
     * @param list<TestItemDto> $items
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $name,
        #[Assert\Type(TestMetadataDto::class)]
        public TestMetadataDto $metadata,
        #[Assert\Type('array')]
        #[Assert\All([
            new Assert\Type(TestItemDto::class),
        ])]
        public array $items,
    ) {}
}
