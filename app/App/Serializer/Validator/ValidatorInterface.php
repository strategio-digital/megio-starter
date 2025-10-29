<?php
declare(strict_types=1);

namespace App\App\Serializer\Validator;

/**
 * @phpstan-type ValidationErrors array<int|string, mixed>
 */
interface ValidatorInterface
{
    /**
     * @param class-string $dtoClass
     * @param array<int|string, mixed> $data
     *
     * @return ValidationErrors
     */
    public function validate(
        string $dtoClass,
        array $data,
    ): array;
}
