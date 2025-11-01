<?php
declare(strict_types=1);

namespace App\App\Serializer\Normalizer;

use BackedEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use UnitEnum;

use function assert;

class PlainEnumNormalizer implements NormalizerInterface
{
    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): string {
        assert($data instanceof UnitEnum === true);
        return $data->name;
    }

    public function supportsNormalization(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $data instanceof UnitEnum && ($data instanceof BackedEnum) === false;
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
