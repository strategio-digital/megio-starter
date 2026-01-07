---
layout: 'page'
uri: '/http/requests'
position: 3
slug: 'http-requests'
parent: 'http'
navTitle: 'Request Handlers'
title: 'Request Handlers'
description: 'REST API request handlers with DTO validation and JSON responses.'
---

# Request Handlers

REST API request handlers with DTO validation and JSON responses.

## How It Works

1. **Router** matches URL to Request Handler class
2. **DI container** instantiates handler with dependencies
3. **`__invoke()`** receives Symfony Request
4. **`requestToDto()`** validates and deserializes JSON body
5. **Facade** processes business logic
6. **`json()`** or **`error()`** returns JSON response

## Directory Structure

```
app/DomainName/
└── Http/
    └── Request/
        ├── LoginRequest.php        # Request handler
        └── Dto/
            └── UserLoginDto.php    # Validation DTO
```

## Creating a Request Handler

### 1. Create DTO class

Place in `app/{Domain}/Http/Request/Dto/`:

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

### 2. Create Request Handler

Place in `app/{Domain}/Http/Request/`:

```php
<?php
declare(strict_types=1);

namespace App\User\Http\Request;

use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\UserAuthFacade;
use App\User\Http\Request\Dto\UserLoginDto;
use Megio\Http\Request\AbstractRequest;
use Megio\Http\Serializer\RequestSerializerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginRequest extends AbstractRequest
{
    public function __construct(
        private readonly UserAuthFacade $userFacade,
    ) {}

    /**
     * @throws RequestSerializerException
     */
    public function process(Request $request): Response
    {
        $dto = $this->requestToDto(UserLoginDto::class);

        try {
            $result = $this->userFacade->loginUser($dto);
        } catch (UserAuthFacadeException $e) {
            return $this->error([
                'general' => $e->getTranslationKey(),
                'params' => $e->getTranslationParams(),
            ], 403);
        }

        return $this->json([
            'bearer_token' => $result->token->getToken(),
            ...$result->claims,
        ]);
    }
}
```

### 3. Register route

Add to `router/rest.php`:

```php
$routes->add('api.user.login', '/api/v1/{locale}/user/login')
    ->methods(['POST'])
    ->controller(LoginRequest::class)
    ->options(['auth' => false])
    ->requirements(['locale' => PosixResolver::LOCALE_POSIX_PATTERN]);
```

## Request Handler Methods

### requestToDto()

Validates JSON body and deserializes to DTO:

```php
/**
 * @template T
 * @param class-string<T> $class
 * @return T
 * @throws RequestSerializerException
 */
public function requestToDto(string $class)
```

Example:

```php
$dto = $this->requestToDto(UserLoginDto::class);
// $dto is now typed UserLoginDto with validated data
```

**Validation errors** are automatically thrown as `RequestSerializerException`.

### json()

Returns success JSON response:

```php
// Empty response (200 OK)
return $this->json();

// With data
return $this->json(['user_id' => $user->getId()]);

// Custom status
return $this->json(['created' => true], 201);
```

### error()

Returns error JSON response:

```php
// Default 400 Bad Request
return $this->error(['general' => 'error.key']);

// Custom status
return $this->error(['general' => 'error.forbidden'], 403);

// With translation params
return $this->error([
    'general' => 'error.min_length',
    'params' => ['min' => 6],
]);
```

### getRequestData()

Gets raw request data (JSON body + uploaded files):

```php
$data = $this->getRequestData();
```

## DTO Validation

For complete validation documentation, see **[Request Validation](/http/validation)**.

Basic example:

```php
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

## Error Handling

### RequestSerializerException

Thrown when validation fails:

```php
public function process(Request $request): Response
{
    try {
        $dto = $this->requestToDto(UserLoginDto::class);
    } catch (RequestSerializerException $e) {
        // Automatically handled by AbstractRequest.__invoke()
        // Returns validation errors as JSON
    }
    // ...
}
```

The `AbstractRequest` base class handles this automatically.

### TranslatableException

For business logic errors from Facade:

```php
use Megio\Translation\Exception\TranslatableExceptionInterface;

try {
    $this->facade->execute($dto);
} catch (TranslatableExceptionInterface $e) {
    return $this->error([
        'general' => $e->getTranslationKey(),
        'params' => $e->getTranslationParams(),
    ]);
}
```

## Request/Response Flow

```
Client Request (JSON)
        │
        ▼
┌───────────────────┐
│  Request Handler  │
│                   │
│ requestToDto()    │ ◄── Validates & deserializes
│        │          │
│        ▼          │
│     Facade        │ ◄── Business logic
│        │          │
│        ▼          │
│ json() / error()  │ ◄── Returns response
└───────────────────┘
        │
        ▼
Client Response (JSON)
```

## Request Handler Rules

### Responsibility (SRP)

- Validate request data via DTO
- Delegate to Facade for business logic
- Return JSON response
- Handle TranslatableExceptions

### Required Patterns

- Extend `AbstractRequest`
- Create DTO in `Http/Request/Dto/` subdirectory
- DTO implements `RequestDtoInterface`
- DTO is `readonly` class
- Use `requestToDto()` for validation
- Catch `TranslatableExceptionInterface` from Facade

### Forbidden

- Direct EntityManager usage
- Business logic implementation
- Email sending
- Database queries
- Translations (pass keys to frontend)

### Error Response Format

```json
{
    "general": "translation.key",
    "params": {"paramName": "value"},
    "field_name": "field.error.key"
}
```

Frontend translates keys using ICU MessageFormat.
