<?php
declare(strict_types=1);

namespace App\App\Serializer\Validator;

use ReflectionClass;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

readonly class RecursiveValidator implements ValidatorInterface
{
    public function __construct(
        private SymfonyValidatorInterface $symfonyValidator,
        private ReflectionHelperInterface $reflectionHelper,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function validate(
        string $dtoClass,
        array $data,
    ): array {
        // Convert numeric keys to string keys for consistency
        $stringKeyedData = [];
        foreach ($data as $key => $value) {
            $stringKeyedData[(string)$key] = $value;
        }

        return $this->validateRecursively($dtoClass, $stringKeyedData, '');
    }

    /**
     * @param class-string $dtoClass
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function validateRecursively(
        string $dtoClass,
        array $data,
        string $path,
    ): array {
        $errors = [];

        // Get constraints and validate each property
        $constraints = $this->reflectionHelper->getValidationConstraints($dtoClass);
        foreach ($constraints as $propertyName => $propertyConstraints) {
            // If property is missing, get default value from DTO constructor
            if (array_key_exists($propertyName, $data) === false) {
                $defaultValue = $this->reflectionHelper->getDefaultValue($dtoClass, $propertyName);
                $propertyValue = $defaultValue;
            } else {
                $propertyValue = $data[$propertyName];
            }
            $dtoType = $this->reflectionHelper->getDtoTypeForProperty($dtoClass, $propertyName);
            $isArrayOfDtos = $this->reflectionHelper->isArrayOfDtos($dtoClass, $propertyName);

            // Handle nested DTOs
            if ($dtoType !== null && is_array($propertyValue) === true) {
                // Convert to string-keyed array
                $stringKeyedValue = [];
                foreach ($propertyValue as $key => $val) {
                    $stringKeyedValue[(string)$key] = $val;
                }
                $nestedErrors = $this->validateRecursively(
                    $dtoType,
                    $stringKeyedValue,
                    $this->buildFullPath($path, $propertyName),
                );
                $errors = $this->mergeErrors($errors, $nestedErrors);
                continue; // Skip Symfony validation for nested DTOs
            }

            // Handle arrays of DTOs
            if ($isArrayOfDtos === true && is_array($propertyValue) === true) {
                $arrayElementType = $this->getArrayElementType($dtoClass, $propertyName);
                if ($arrayElementType !== null) {
                    // Re-index array to ensure int keys
                    $intKeyedArray = array_values($propertyValue);
                    $arrayErrors = $this->validateArrayOfDtos(
                        $arrayElementType,
                        $intKeyedArray,
                        $this->buildFullPath($path, $propertyName),
                    );
                    $errors = $this->mergeErrors($errors, $arrayErrors);
                }
                continue; // Skip Symfony validation for array DTOs
            }

            // Validate using Symfony constraints (but skip Type constraints for DTOs)
            foreach ($propertyConstraints as $constraint) {
                // Skip Type constraints that reference DTO classes
                if ($constraint instanceof Type) {
                    if (is_string($constraint->type) && ($dtoType !== null || $isArrayOfDtos === true)) {
                        continue; // Skip DTO Type constraints - we handle them above
                    }
                }

                $violations = $this->symfonyValidator->validate($propertyValue, $constraint);
                foreach ($violations as $violation) {
                    $fullPath = $this->buildFullPath($path, $propertyName);
                    $this->setNestedError($errors, $fullPath, (string)$violation->getMessage());
                }
            }
        }

        return $errors;
    }

    private function buildFullPath(
        string $basePath,
        string $propertyPath,
    ): string {
        if ($basePath === '') {
            return $propertyPath;
        }

        return $basePath . '.' . $propertyPath;
    }

    /**
     * @param array<string, mixed> $errors1
     * @param array<string, mixed> $errors2
     *
     * @return array<string, mixed>
     */
    private function mergeErrors(
        array $errors1,
        array $errors2,
    ): array {
        foreach ($errors2 as $key => $value) {
            if (array_key_exists($key, $errors1) === true && is_array($errors1[$key]) === true && is_array(
                $value,
            ) === true) {
                /** @var array<string, mixed> $existingValue */
                $existingValue = $errors1[$key];
                /** @var array<string, mixed> $newValue */
                $newValue = $value;
                $errors1[$key] = $this->mergeErrors($existingValue, $newValue);
            } else {
                $errors1[$key] = $value;
            }
        }

        return $errors1;
    }

    /**
     * @param class-string $dtoClass
     *
     * @return class-string|null
     */
    private function getArrayElementType(
        string $dtoClass,
        string $propertyName,
    ): ?string {
        $reflection = new ReflectionClass($dtoClass);

        if ($reflection->hasProperty($propertyName) === false) {
            return null;
        }

        $property = $reflection->getProperty($propertyName);
        return $this->reflectionHelper->getArrayElementType($property);
    }

    /**
     * @param class-string $dtoClass
     * @param array<int, mixed> $arrayData
     *
     * @return array<string, mixed>
     */
    private function validateArrayOfDtos(
        string $dtoClass,
        array $arrayData,
        string $path,
    ): array {
        $errors = [];

        foreach ($arrayData as $index => $itemData) {
            if (is_array($itemData) === false) {
                continue;
            }

            // Convert to string-keyed array
            $stringKeyedItemData = [];
            foreach ($itemData as $key => $val) {
                $stringKeyedItemData[(string)$key] = $val;
            }
            $itemErrors = $this->validateRecursively(
                $dtoClass,
                $stringKeyedItemData,
                $this->buildFullPath($path, (string)$index),
            );
            $errors = $this->mergeErrors($errors, $itemErrors);
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function setNestedError(
        array &$errors,
        string $path,
        string $message,
    ): void {
        $pathParts = explode('.', $path);
        $current = &$errors;

        for ($i = 0; $i < count($pathParts) - 1; $i++) {
            $part = $pathParts[$i];
            // Convert numeric strings to integers for proper array indexing
            $key = is_numeric($part) ? (int)$part : $part;

            if (is_array($current) === false) {
                return;
            }
            if (array_key_exists($key, $current) === false) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        $lastPart = $pathParts[count($pathParts) - 1];
        $finalKey = is_numeric($lastPart) ? (int)$lastPart : $lastPart;

        if (is_array($current) === true) {
            $current[$finalKey] = $message;
        }
    }
}
