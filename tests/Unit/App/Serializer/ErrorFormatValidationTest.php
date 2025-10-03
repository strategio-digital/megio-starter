<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer;

use App\App\Serializer\RequestSerializer;
use App\App\Serializer\RequestSerializerException;
use App\App\Serializer\Validator\RecursiveValidator;
use App\App\Serializer\Validator\ReflectionHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tests\Unit\App\Serializer\Fixtures\Complex\TestComplexNestedDto;
use Tests\Unit\App\Serializer\Fixtures\Complex\TestMetadataDto;
use Tests\Unit\App\Serializer\Fixtures\Simple\TestUserDto;

class ErrorFormatValidationTest extends TestCase
{
    public function testReturnsSimpleValidationErrors(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);
        $serializer = new RequestSerializer($validator);

        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'age' => 15,
        ];

        try {
            $serializer->denormalize(TestUserDto::class, $invalidData);
            $this->fail('Exception should have been thrown');
        } catch (RequestSerializerException $e) {
            $errors = $e->getErrors();

            $this->assertIsArray($errors);
            $this->assertArrayHasKey('name', $errors);
            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('age', $errors);
            $this->assertIsString($errors['name']);
            $this->assertIsString($errors['email']);
            $this->assertIsString($errors['age']);
            $this->assertNotEmpty($errors['name']);
            $this->assertNotEmpty($errors['email']);
            $this->assertNotEmpty($errors['age']);
        }
    }

    public function testReturnsNestedValidationErrors(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);
        $serializer = new RequestSerializer($validator);

        $invalidData = [
            'name' => 'Valid Name',
            'metadata' => [
                'title' => '',
                'priority' => 150,
                'author' => [
                    'firstName' => '',
                    'lastName' => 'Valid Last Name',
                    'email' => 'invalid-email',
                ],
            ],
            'items' => [
                [
                    'code' => 'VALID001',
                    'price' => 29.99,
                ],
                [
                    'code' => '',
                    'price' => -5.0,
                ],
            ],
        ];

        try {
            $serializer->denormalize(TestComplexNestedDto::class, $invalidData);
            $this->fail('Exception should have been thrown');
        } catch (RequestSerializerException $e) {
            $errors = $e->getErrors();

            $this->assertIsArray($errors);
            $this->assertArrayHasKey('metadata', $errors);
            $this->assertArrayHasKey('items', $errors);
            $this->assertIsArray($errors['metadata']);
            $this->assertIsArray($errors['items']);
            $this->assertArrayHasKey('title', $errors['metadata']);
            $this->assertArrayHasKey('priority', $errors['metadata']);
            $this->assertArrayHasKey('author', $errors['metadata']);
            $this->assertIsString($errors['metadata']['title']);
            $this->assertIsString($errors['metadata']['priority']);
            $this->assertIsArray($errors['metadata']['author']);
            $this->assertArrayHasKey('firstName', $errors['metadata']['author']);
            $this->assertArrayHasKey('email', $errors['metadata']['author']);
            $this->assertIsString($errors['metadata']['author']['firstName']);
            $this->assertIsString($errors['metadata']['author']['email']);
            $this->assertArrayHasKey(1, $errors['items']);
            $this->assertIsArray($errors['items'][1]);
            $this->assertArrayHasKey('code', $errors['items'][1]);
            $this->assertArrayHasKey('price', $errors['items'][1]);
            $this->assertIsString($errors['items'][1]['code']);
            $this->assertIsString($errors['items'][1]['price']);
        }
    }

    public function testHandlesDeeplyNestedErrors(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);
        $serializer = new RequestSerializer($validator);

        $invalidData = [
            'name' => 'Valid Name',
            'metadata' => [
                'title' => 'Valid Title',
                'priority' => 50,
                'author' => [
                    'firstName' => 'Valid First',
                    'lastName' => '',
                    'email' => 'invalid-email-format',
                ],
            ],
            'items' => [],
        ];

        try {
            $serializer->denormalize(TestComplexNestedDto::class, $invalidData);
            $this->fail('Exception should have been thrown');
        } catch (RequestSerializerException $e) {
            $errors = $e->getErrors();

            $this->assertIsArray($errors);
            $this->assertArrayHasKey('metadata', $errors);
            $this->assertIsArray($errors['metadata']);
            $this->assertArrayNotHasKey('name', $errors);
            $this->assertArrayHasKey('author', $errors['metadata']);
            $this->assertArrayNotHasKey('title', $errors['metadata']);
            $this->assertArrayNotHasKey('priority', $errors['metadata']);
            $this->assertIsArray($errors['metadata']['author']);
            $this->assertArrayHasKey('lastName', $errors['metadata']['author']);
            $this->assertArrayHasKey('email', $errors['metadata']['author']);
            $this->assertIsString($errors['metadata']['author']['lastName']);
            $this->assertIsString($errors['metadata']['author']['email']);
            $this->assertArrayNotHasKey('firstName', $errors['metadata']['author']);
        }
    }

    public function testHandlesArrayIndexErrors(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);
        $serializer = new RequestSerializer($validator);

        $invalidData = [
            'name' => 'Valid Name',
            'metadata' => [
                'title' => 'Valid Title',
                'priority' => 50,
                'author' => [
                    'firstName' => 'Valid First',
                    'lastName' => 'Valid Last',
                    'email' => 'valid@email.com',
                ],
            ],
            'items' => [
                [
                    'code' => 'VALID001',
                    'price' => 29.99,
                ],
                [
                    'code' => '',
                    'price' => 19.99,
                ],
                [
                    'code' => 'VALID003',
                    'price' => -10.0,
                ],
            ],
        ];

        try {
            $serializer->denormalize(TestComplexNestedDto::class, $invalidData);
            $this->fail('Exception should have been thrown');
        } catch (RequestSerializerException $e) {
            $errors = $e->getErrors();

            $this->assertIsArray($errors);
            $this->assertArrayHasKey('items', $errors);
            $this->assertIsArray($errors['items']);
            $this->assertArrayHasKey(1, $errors['items']);
            $this->assertArrayHasKey(2, $errors['items']);
            $this->assertArrayNotHasKey(0, $errors['items']);
            $this->assertIsArray($errors['items'][1]);
            $this->assertArrayHasKey('code', $errors['items'][1]);
            $this->assertIsString($errors['items'][1]['code']);
            $this->assertIsArray($errors['items'][2]);
            $this->assertArrayHasKey('price', $errors['items'][2]);
            $this->assertIsString($errors['items'][2]['price']);
        }
    }

    public function testValidatesWithoutErrorsWhenDataIsValid(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);
        $serializer = new RequestSerializer($validator);

        $validData = [
            'name' => 'Valid Name',
            'metadata' => [
                'title' => 'Valid Title',
                'priority' => 50,
                'author' => [
                    'firstName' => 'Valid First',
                    'lastName' => 'Valid Last',
                    'email' => 'valid@email.com',
                ],
            ],
            'items' => [
                [
                    'code' => 'VALID001',
                    'price' => 29.99,
                ],
                [
                    'code' => 'VALID002',
                    'price' => 19.99,
                ],
            ],
        ];

        $result = $serializer->denormalize(TestComplexNestedDto::class, $validData);

        $this->assertInstanceOf(TestComplexNestedDto::class, $result);
        $this->assertSame('Valid Name', $result->name);
        $this->assertInstanceOf(TestMetadataDto::class, $result->metadata);
        $this->assertIsArray($result->items);
        $this->assertCount(2, $result->items);
    }
}
