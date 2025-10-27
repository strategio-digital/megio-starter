<?php
declare(strict_types=1);

namespace App\App\Serializer\Normalizer;

use BackedEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use UnitEnum;

class PlainEnumNormalizer implements NormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): string {
        assert($data instanceof UnitEnum);
        return $data->name;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $data instanceof UnitEnum && !$data instanceof BackedEnum;
    }

    /**
     * @return array<string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            UnitEnum::class => true,
        ];
    }
}
