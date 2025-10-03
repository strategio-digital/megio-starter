<?php
declare(strict_types=1);

namespace App\App\Serializer\Validator;

use ReflectionProperty;
use Symfony\Component\Validator\Constraint;

interface ReflectionHelperInterface
{
    /**
     * @param class-string $dtoClass
     *
     * @return array<string, list<Constraint>>
     */
    public function getValidationConstraints(string $dtoClass): array;

    /**
     * @param class-string $dtoClass
     *
     * @return class-string|null
     */
    public function getDtoTypeForProperty(string $dtoClass, string $propertyName): ?string;

    /**
     * @param class-string $dtoClass
     */
    public function isArrayOfDtos(string $dtoClass, string $propertyName): bool;

    /**
     * @return class-string|null
     */
    public function getArrayElementType(ReflectionProperty $property): ?string;
}
