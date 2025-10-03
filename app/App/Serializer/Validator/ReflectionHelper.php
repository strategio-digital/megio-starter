<?php
declare(strict_types=1);

namespace App\App\Serializer\Validator;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Type;

readonly class ReflectionHelper implements ReflectionHelperInterface
{
    public function getValidationConstraints(string $dtoClass): array
    {
        $reflection = new ReflectionClass($dtoClass);
        $constraints = [];

        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            $propertyConstraints = [];

            foreach ($property->getAttributes() as $attribute) {
                $attributeInstance = $attribute->newInstance();
                if ($attributeInstance instanceof Constraint) {
                    $propertyConstraints[] = $attributeInstance;
                }
            }

            if (count($propertyConstraints) > 0) {
                $constraints[$propertyName] = $propertyConstraints;
            }
        }

        return $constraints;
    }

    public function getDtoTypeForProperty(string $dtoClass, string $propertyName): ?string
    {
        $reflection = new ReflectionClass($dtoClass);

        if ($reflection->hasProperty($propertyName) === false) {
            return null;
        }

        $property = $reflection->getProperty($propertyName);
        $type = $property->getType();

        if ($type instanceof ReflectionNamedType === false) {
            return null;
        }

        $typeName = $type->getName();

        if ($this->isBuiltInType($typeName) === true) {
            return null;
        }

        if (class_exists($typeName) === false) {
            return null;
        }

        return $typeName;
    }

    public function isArrayOfDtos(string $dtoClass, string $propertyName): bool
    {
        $reflection = new ReflectionClass($dtoClass);

        if ($reflection->hasProperty($propertyName) === false) {
            return false;
        }

        $property = $reflection->getProperty($propertyName);

        // Check for standard Symfony All constraint
        foreach ($property->getAttributes() as $attribute) {
            $attributeInstance = $attribute->newInstance();
            if ($attributeInstance instanceof All) {
                $constraints = $attributeInstance->constraints;
                if (is_array($constraints) === true) {
                    foreach ($constraints as $constraint) {
                        if ($constraint instanceof Type && is_string($constraint->type)) {
                            if ($this->isBuiltInType($constraint->type) === false && class_exists($constraint->type) === true) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    public function getArrayElementType(ReflectionProperty $property): ?string
    {
        // Check for standard Symfony All constraint
        foreach ($property->getAttributes() as $attribute) {
            $attributeInstance = $attribute->newInstance();
            if ($attributeInstance instanceof All) {
                $constraints = $attributeInstance->constraints;
                if (is_array($constraints) === true) {
                    foreach ($constraints as $constraint) {
                        if ($constraint instanceof Type && is_string($constraint->type)) {
                            if (class_exists($constraint->type) === true) {
                                return $constraint->type;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    private function isBuiltInType(string $typeName): bool
    {
        $builtInTypes = [
            'string', 'int', 'float', 'bool', 'array',
            'object', 'resource', 'null', 'callable',
            'iterable', 'mixed', 'void', 'never',
        ];

        return in_array($typeName, $builtInTypes, true);
    }
}
