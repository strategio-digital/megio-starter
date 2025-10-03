<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer\Fixtures\Simple;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TestUserWithTagsDto
{
    /**
     * @param list<TestTagDto> $tags
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $name,
        #[Assert\Type('array')]
        #[Assert\All([
            new Assert\Type(TestTagDto::class),
        ])]
        public array $tags,
    ) {}
}
