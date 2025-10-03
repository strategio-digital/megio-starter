<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer;

use App\App\Serializer\Validator\RecursiveValidator;
use App\App\Serializer\Validator\ReflectionHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tests\Unit\App\Serializer\Fixtures\Simple\TestNestedUserDto;
use Tests\Unit\App\Serializer\Fixtures\Simple\TestUserDto;
use Tests\Unit\App\Serializer\Fixtures\Simple\TestUserWithTagsDto;

class RecursiveValidatorTest extends TestCase
{
    public function testValidatesSimpleDtos(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);

        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
        ];

        $errors = $validator->validate(TestUserDto::class, $validData);

        $this->assertSame([], $errors);
    }

    public function testReturnsValidationErrorsForInvalidData(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);

        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'age' => 15,
        ];

        $errors = $validator->validate(TestUserDto::class, $invalidData);

        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('age', $errors);
        $this->assertIsString($errors['name']);
        $this->assertIsString($errors['email']);
        $this->assertIsString($errors['age']);
    }

    public function testValidatesNestedDtos(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);

        $validData = [
            'name' => 'John Doe',
            'address' => [
                'street' => 'Main Street 123',
                'city' => 'New York',
                'zipCode' => '12345',
            ],
        ];

        $errors = $validator->validate(TestNestedUserDto::class, $validData);

        $this->assertSame([], $errors);
    }

    public function testReturnsNestedValidationErrors(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);

        $invalidData = [
            'name' => 'John Doe',
            'address' => [
                'street' => '',
                'city' => '',
                'zipCode' => '123',
            ],
        ];

        $errors = $validator->validate(TestNestedUserDto::class, $invalidData);

        $this->assertArrayHasKey('address', $errors);
        $this->assertIsArray($errors['address']);
        $this->assertArrayHasKey('street', $errors['address']);
        $this->assertArrayHasKey('city', $errors['address']);
        $this->assertArrayHasKey('zipCode', $errors['address']);
        $this->assertIsString($errors['address']['street']);
        $this->assertIsString($errors['address']['city']);
        $this->assertIsString($errors['address']['zipCode']);
    }

    public function testValidatesBothTopLevelAndNestedErrors(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);

        $invalidData = [
            'name' => '',
            'address' => [
                'street' => '',
                'city' => 'Valid City',
                'zipCode' => 'toolongzipcode',
            ],
        ];

        $errors = $validator->validate(TestNestedUserDto::class, $invalidData);

        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('address', $errors);
        $this->assertIsString($errors['name']);
        $this->assertIsArray($errors['address']);
        $this->assertArrayHasKey('street', $errors['address']);
        $this->assertArrayHasKey('zipCode', $errors['address']);
        $this->assertArrayNotHasKey('city', $errors['address']);
    }

    public function testValidatesArrayOfDtos(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);

        $validData = [
            'name' => 'John Doe',
            'tags' => [
                [
                    'name' => 'Developer',
                    'color' => 'blue',
                ],
                [
                    'name' => 'Manager',
                    'color' => 'red',
                ],
            ],
        ];

        $errors = $validator->validate(TestUserWithTagsDto::class, $validData);

        $this->assertSame([], $errors);
    }

    public function testReturnsValidationErrorsForArrayOfDtos(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);

        $invalidData = [
            'name' => 'John Doe',
            'tags' => [
                [
                    'name' => '',
                    'color' => 'blue',
                ],
                [
                    'name' => 'Manager',
                    'color' => '',
                ],
            ],
        ];

        $errors = $validator->validate(TestUserWithTagsDto::class, $invalidData);

        $this->assertArrayHasKey('tags', $errors);
        $this->assertIsArray($errors['tags']);
        $this->assertArrayHasKey(0, $errors['tags']);
        $this->assertArrayHasKey(1, $errors['tags']);
        $this->assertIsArray($errors['tags'][0]);
        $this->assertIsArray($errors['tags'][1]);
        $this->assertArrayHasKey('name', $errors['tags'][0]);
        $this->assertArrayHasKey('color', $errors['tags'][1]);
        $this->assertIsString($errors['tags'][0]['name']);
        $this->assertIsString($errors['tags'][1]['color']);
    }

    public function testHandlesMixedValidAndInvalidArrayItems(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);

        $invalidData = [
            'name' => 'John Doe',
            'tags' => [
                [
                    'name' => 'Valid Tag',
                    'color' => 'blue',
                ],
                [
                    'name' => '',
                    'color' => 'red',
                ],
                [
                    'name' => 'Another Valid',
                    'color' => 'green',
                ],
            ],
        ];

        $errors = $validator->validate(TestUserWithTagsDto::class, $invalidData);

        $this->assertArrayHasKey('tags', $errors);
        $this->assertIsArray($errors['tags']);
        $this->assertArrayNotHasKey(0, $errors['tags']);
        $this->assertArrayHasKey(1, $errors['tags']);
        $this->assertArrayNotHasKey(2, $errors['tags']);
        $this->assertIsArray($errors['tags'][1]);
        $this->assertArrayHasKey('name', $errors['tags'][1]);
    }

    public function testReturnsEmptyArrayWhenNoErrorsExist(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);

        $validData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'age' => 30,
        ];

        $errors = $validator->validate(TestUserDto::class, $validData);

        $this->assertSame([], $errors);
        $this->assertCount(0, $errors);
    }
}
