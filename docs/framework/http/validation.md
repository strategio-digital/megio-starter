---
layout: 'page'
uri: '/http/validation'
position: 4
slug: 'http-validation'
parent: 'http'
navTitle: 'Request Validation'
title: 'Request Validation'
description: 'DTO validation with Symfony Validator attributes and RequestSerializer.'
---

# Request Validation

DTO validation with Symfony Validator attributes and RequestSerializer.

## How It Works

1. **Request Handler** calls `requestToDto(DtoClass::class)`
2. **RequestSerializer** decodes JSON from request body
3. **RecursiveValidator** validates data against DTO attributes
4. **Validation errors** throw `RequestSerializerException`
5. **Valid data** is deserialized into typed DTO object

## Directory Structure

```
app/DomainName/
└── Http/
    └── Request/
        └── Dto/
            └── UserLoginDto.php
```

## Creating Validation DTOs

### Basic DTO

```php
<?php
declare(strict_types=1);

namespace App\User\Http\Request\Dto;

use Megio\Http\Serializer\Dto\RequestDtoInterface;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UserLoginDto implements RequestDtoInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        public string $password,
    ) {}
}
```

### Required Patterns

- Implement `RequestDtoInterface`
- Use `readonly` class
- Constructor property promotion
- Symfony Validator attributes

## Validation Attributes

### String Validation

```php
// Required non-empty string
#[Assert\NotBlank]
public string $name;

// Email format
#[Assert\NotBlank]
#[Assert\Email]
public string $email;

// Length constraints
#[Assert\NotBlank]
#[Assert\Length(min: 6, max: 32)]
public string $password;

// Regex pattern
#[Assert\NotBlank]
#[Assert\Regex(pattern: '/^[a-z0-9_]+$/')]
public string $username;

// URL format
#[Assert\NotBlank]
#[Assert\Url]
public string $website;
```

### Nullable Fields

```php
// Optional string (can be null)
#[Assert\Type(['null', 'string'])]
public ?string $note = null;

// Optional with length constraint when provided
#[Assert\Type(['null', 'string'])]
#[Assert\Length(max: 500)]
public ?string $description = null;
```

### Numeric Validation

```php
// Integer type
#[Assert\NotBlank]
#[Assert\Type('int')]
public int $quantity;

// Range validation
#[Assert\NotBlank]
#[Assert\Type('int')]
#[Assert\GreaterThan(0)]
#[Assert\LessThanOrEqual(100)]
public int $percentage;

// Positive number
#[Assert\NotBlank]
#[Assert\Type('float')]
#[Assert\Positive]
public float $price;
```

### DateTime Validation

```php
// ISO 8601 format (YYYY-MM-DDTHH:MM)
#[Assert\NotBlank]
#[Assert\DateTime(format: 'Y-m-d\TH:i')]
public string $scheduledAt;

// Date only
#[Assert\NotBlank]
#[Assert\DateTime(format: 'Y-m-d')]
public string $birthDate;

// Optional datetime
#[Assert\Type(['null', 'string'])]
#[Assert\DateTime(format: 'Y-m-d\TH:i')]
public ?string $expiresAt = null;
```

### Choice Validation

```php
// Single choice from list
#[Assert\NotBlank]
#[Assert\Choice(['active', 'inactive', 'pending'])]
public string $status;

// Multiple choices
#[Assert\NotBlank]
#[Assert\Choice(
    choices: ['admin', 'editor', 'viewer'],
    multiple: true,
)]
public array $roles;
```

### Boolean Validation

```php
// Required boolean
#[Assert\NotNull]
#[Assert\Type('bool')]
public bool $isActive;

// Optional boolean with default
#[Assert\Type(['null', 'bool'])]
public ?bool $sendNotification = true;
```

### Array Validation

```php
// Non-empty array
#[Assert\NotBlank]
#[Assert\Count(min: 1)]
public array $tags;

// Array with max items
#[Assert\Count(max: 10)]
public array $attachments;

// Array of specific type
/** @var string[] */
#[Assert\All([
    new Assert\NotBlank(),
    new Assert\Type('string'),
])]
public array $emails;
```

### Nested Objects

```php
readonly class OrderDto implements RequestDtoInterface
{
    public function __construct(
        // Single nested object
        #[Assert\NotBlank]
        #[Assert\Valid]
        public AddressDto $shippingAddress,

        // Array of nested objects
        /** @var OrderItemDto[] */
        #[Assert\Valid]
        #[Assert\Count(min: 1)]
        public array $items,
    ) {}
}

readonly class AddressDto implements RequestDtoInterface
{
    public function __construct(
        #[Assert\NotBlank]
        public string $street,

        #[Assert\NotBlank]
        public string $city,

        #[Assert\NotBlank]
        #[Assert\Length(exactly: 5)]
        public string $zipCode,
    ) {}
}
```

## Using in Request Handlers

See [Request Handlers](/http/requests) for complete usage.

```php
public function process(Request $request): Response
{
    // Validates and deserializes JSON body
    $dto = $this->requestToDto(UserLoginDto::class);

    // $dto is typed and validated
    // Use $dto->email, $dto->password, etc.
}
```

## Error Handling

### RequestSerializerException

Thrown automatically when validation fails:

```php
try {
    $dto = $this->requestToDto(UserLoginDto::class);
} catch (RequestSerializerException $e) {
    // $e->getErrors() returns validation errors
    return $this->error($e->getErrors());
}
```

The `AbstractRequest` base class handles this automatically.

### Error Response Format

```json
{
    "email": "validator.email.invalid",
    "password": "validator.not_blank",
    "quantity": "validator.greater_than"
}
```

Keys are field names, values are translation keys.

## Translated Error Messages

Validation error messages use translation keys from `locale/validator.locale.{POSIX}.neon`:

```yaml
# locale/validator.locale.en_US.neon
validator:
    not_blank: 'This field is required.'
    email:
        invalid: 'Enter a valid email address.'
    length:
        min: 'Must be at least {limit} characters.'
        max: 'Must be at most {limit} characters.'
    greater_than: 'Must be greater than {compared_value}.'
```

Frontend receives translation keys and handles translation via ICU MessageFormat.

## Validation Rules

### DTO Requirements

- Implement `RequestDtoInterface`
- Use `readonly` class
- Constructor property promotion
- All public properties

### Where to Validate

- **Request DTOs** - API input validation
- **Facade DTOs** - Internal data transfer (optional)

### Where NOT to Validate

- **Entities** - Use database constraints
- **Repositories** - No validation
- **Controllers** - Use Request Handlers for API

### Common Constraints Reference

| Constraint | Usage |
|------------|-------|
| `NotBlank` | Required field |
| `NotNull` | Cannot be null (use for bool) |
| `Type` | Type check (`string`, `int`, `bool`, `array`) |
| `Email` | Email format |
| `Length` | String length (min, max, exactly) |
| `Regex` | Pattern matching |
| `Url` | URL format |
| `Choice` | Value from allowed list |
| `GreaterThan` | Numeric comparison |
| `LessThanOrEqual` | Numeric comparison |
| `Positive` | Positive number |
| `DateTime` | DateTime format |
| `Count` | Array item count |
| `Valid` | Validate nested object |
| `All` | Validate each array item |
