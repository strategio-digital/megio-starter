<?php declare(strict_types=1);

namespace Tests\Unit\App\Serializer;

use App\App\Serializer\RequestSerializer;
use App\App\Serializer\RequestSerializerException;
use App\App\Serializer\Validator\RecursiveValidator;
use App\App\Serializer\Validator\ReflectionHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tests\Unit\App\Serializer\Fixtures\Nested\TestNestedDto;
use Tests\Unit\App\Serializer\Fixtures\Nested\TestNestedUserDto;

class NestedDtoValidationTest extends TestCase
{
    public function testHandlesNestedDtos(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);
        $serializer = new RequestSerializer($validator);

        $dataWithNestedArray = [
            'name' => 'Test entry',
            'sharedWith' => [
                'accepted' => true,
                'user_id' => 123,
                'user_name' => 'John Doe',
            ],
        ];

        $result = $serializer->denormalizeFromArray(TestNestedDto::class, $dataWithNestedArray);

        $this->assertInstanceOf(TestNestedDto::class, $result);
        $this->assertSame('Test entry', $result->name);
        $this->assertInstanceOf(TestNestedUserDto::class, $result->sharedWith);
        $this->assertSame(123, $result->sharedWith->user_id);
    }

    public function testAcceptsValidDtoInstances(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);
        $serializer = new RequestSerializer($validator);

        $sharedWithInstance = new TestNestedUserDto(
            accepted: true,
            user_id: 123,
            user_name: 'John Doe',
        );

        $dataWithValidInstance = [
            'name' => 'Test entry',
            'sharedWith' => $sharedWithInstance,
        ];

        $dto = $serializer->denormalizeFromArray(TestNestedDto::class, $dataWithValidInstance);

        $this->assertInstanceOf(TestNestedDto::class, $dto);
        $this->assertSame('Test entry', $dto->name);
        $this->assertInstanceOf(TestNestedUserDto::class, $dto->sharedWith);
    }

    public function testValidatesNestedDtoConstraints(): void
    {
        $symfonyValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $reflectionHelper = new ReflectionHelper();
        $validator = new RecursiveValidator($symfonyValidator, $reflectionHelper);
        $serializer = new RequestSerializer($validator);

        $dataWithInvalidNestedData = [
            'name' => 'Test entry',
            'sharedWith' => [
                'accepted' => true,
                'user_id' => 'invalid_user_id', // This should be int, but is string
                'user_name' => 'John Doe',
            ],
        ];

        // The new recursive validator catches nested validation errors
        $this->expectException(RequestSerializerException::class);
        $serializer->denormalizeFromArray(TestNestedDto::class, $dataWithInvalidNestedData);
    }
}
