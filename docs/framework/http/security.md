---
layout: 'page'
uri: '/http/security'
position: 5
slug: 'http-security'
parent: 'http'
navTitle: 'Security'
title: 'Security & Authentication'
description: 'JWT-based authentication with roles, resources, and route protection.'
---

# Security & Authentication

JWT-based authentication with roles, resources, and route protection.

## How It Works

1. **User** logs in with credentials
2. **JWT token** is created with claims (user, roles, resources)
3. **Token** is stored in database and sent to client
4. **AuthRequest subscriber** validates token on each request
5. **AuthUser** service provides current user info

## Authentication Flow

```
Login Request → Validate Credentials → Create JWT → Store Token → Return Token
                                                          ↓
Protected Request → Validate JWT → Load User → Set AuthUser → Process Request
```

## JWT Token

### Token Structure

```json
{
  "iss": "strategio.dev",
  "aud": "strategio-megio-apps",
  "iat": 1704067200,
  "exp": 1704153600,
  "bearer_token_id": "uuid-of-token",
  "user": {
    "id": "user-uuid",
    "email": "user@example.com",
    "roles": ["user", "admin"],
    "resources": ["product.read", "product.write"]
  }
}
```

### Token Expiration

Default: 24 hours (configurable in login facade)

## Route Protection

### REST Routes (API)

```php
// router/rest.php

// Protected route (default)
$routes->add('api.product.list', '/api/v1/products')
    ->methods(['GET'])
    ->controller([ProductListRequest::class, 'process']);

// Public route
$routes->add('api.auth.login', '/api/v1/auth/login')
    ->methods(['POST'])
    ->controller([LoginRequest::class, 'process'])
    ->options(['auth' => false]);
```

### Web Routes

```php
// router/web.php

// Protected page
$routes->add('dashboard', '/{locale}/dashboard')
    ->methods(['GET'])
    ->controller([DashboardController::class, 'dashboard']);

// Public page
$routes->add('login', '/{locale}/login')
    ->methods(['GET'])
    ->controller([UserController::class, 'login'])
    ->options(['auth' => false]);
```

## Using AuthUser

### In Request Handler

```php
<?php
declare(strict_types=1);

namespace App\Product\Http\Request;

use Megio\Http\Request\AbstractRequest;
use Megio\Security\Auth\AuthUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProductListRequest extends AbstractRequest
{
    public function __construct(
        private readonly AuthUser $authUser,
    ) {}

    public function process(Request $request): Response
    {
        $user = $this->authUser->get();

        if ($user === null) {
            return $this->error(['errors' => ['Not authenticated']], 401);
        }

        // Get user info
        $userId = $user->getId();
        $email = $user->getEmail();

        // Check roles
        $roles = $this->authUser->getRoles();
        // ['user', 'admin']

        // Check resources (permissions)
        $resources = $this->authUser->getResources();
        // ['product.read', 'product.write']

        return $this->json(['userId' => $userId]);
    }
}
```

### In Facade

```php
final readonly class CreateProductFacade
{
    public function __construct(
        private AuthUser $authUser,
        private EntityManager $em,
    ) {}

    public function execute(CreateProductDto $dto): Product
    {
        $user = $this->authUser->get();

        $product = new Product();
        $product->setCreatedBy($user);

        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }
}
```

## JWTResolver

### Create Token

```php
use Megio\Security\JWT\JWTResolver;

final readonly class LoginUserFacade
{
    public function __construct(
        private JWTResolver $jwt,
        private EntityManager $em,
    ) {}

    public function execute(LoginDto $dto): string
    {
        // Validate user...

        $expiresAt = new DateTimeImmutable('+24 hours');

        $token = $this->jwt->createToken($expiresAt, [
            'bearer_token_id' => $authToken->getId(),
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $roleNames,
                'resources' => $resourceNames,
            ],
        ]);

        return $token;
    }
}
```

### Validate Token

```php
// Check if token is valid
$isValid = $this->jwt->isTrustedToken($bearerToken);

// Parse token to get claims
$parsedToken = $this->jwt->parseToken($bearerToken);
$claims = $parsedToken->claims();
$userId = $claims->get('user')['id'];
```

## IAuthenticable Interface

Entities that can be authenticated must implement:

```php
<?php
declare(strict_types=1);

namespace App\User\Database\Entity;

use Doctrine\Common\Collections\Collection;
use Megio\Database\Entity\Auth\Role;
use Megio\Database\Interface\IAuthenticable;

class User implements IAuthenticable
{
    public function getId(): string { ... }

    public function getEmail(): string { ... }

    public function getPassword(): string { ... }

    public function setPassword(string $password): void { ... }

    /** @return Collection<int, Role> */
    public function getRoles(): Collection { ... }

    public function addRole(Role $role): void { ... }

    public function removeRole(Role $role): void { ... }
}
```

## Roles & Resources

### Role Entity

```php
// Megio\Database\Entity\Auth\Role
$role = new Role();
$role->setName('admin');
$role->addResource($resource);
```

### Resource Entity

```php
// Megio\Database\Entity\Auth\Resource
$resource = new Resource();
$resource->setName('product.write');
```

### Checking Permissions

```php
// In Request Handler or Facade
$resources = $this->authUser->getResources();

if (in_array('product.write', $resources, true) === false) {
    throw new AccessDeniedException('Missing permission');
}
```

## Auth Subscribers

### Built-in Subscribers

| Subscriber | Purpose |
|------------|---------|
| `AuthRequest` | Validates JWT for protected routes |
| `AuthRouteRequest` | Route-specific auth rules |
| `AuthCollectionRequest` | Collection CRUD authorization |
| `AuthCollectionFormRequest` | Collection form authorization |

### Auth Response Headers

When authentication fails:

```
X-Auth-Reject-Reason: invalid_credentials
X-Auth-Reject-Reason: invalid_permissions
```

## Configuration

### Strict Resource Checking

```dotenv
# Disable if you don't want to check resources on every request
AUTH_STRICT_RESOURCES=false
```

When enabled (default), if user's permissions change after token was issued, the request is rejected.

## Security Rules

### Required Patterns

- Use `AuthUser` service (not direct token parsing)
- Set `auth => false` only for public routes
- Check permissions before sensitive operations
- Use IAuthenticable interface for user entities

### Forbidden

- Storing passwords in plain text
- Logging JWT tokens
- Hardcoding user IDs
- Bypassing auth checks

### Token Storage

| Client Type | Storage |
|-------------|---------|
| Web SPA | HttpOnly cookie or localStorage |
| Mobile app | Secure storage |
| API client | Environment variable |
