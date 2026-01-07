---
layout: 'page'
uri: '/http/routing'
position: 1
slug: 'http-routing'
parent: 'http'
navTitle: 'Routing'
title: 'Routing'
description: 'URL routing with Symfony routing, locale support, and authentication options.'
---

# Routing

URL routing with Symfony routing, locale support, and authentication options.

## How It Works

1. **Request** hits `public/index.php`
2. **HttpKernel** loads routes from `router/app.php`
3. **Router** matches URL to route definition
4. **Subscribers** intercept (CORS, Auth, Translation detection)
5. **Controller/Request** is instantiated via DI container
6. **Response** is sent back

## Directory Structure

```
router/
├── app.php          # Main entry - imports rest.php and web.php
├── rest.php         # REST API routes (Request handlers)
└── web.php          # Web routes (Controllers for Latte)
```

## Route Types

### Web Routes (Controllers)

For pages that render Latte templates. Define in `router/web.php`:

```php
<?php declare(strict_types=1);

use App\User\Http\Controller\UserController;
use Megio\Translation\Resolver\PosixResolver;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    // Homepage without locale
    $routes->add('home', '/')
        ->methods(['GET'])
        ->controller([HomeController::class, 'index'])
        ->options(['auth' => false]);

    // Homepage with locale
    $routes->add('home.locale', '/{locale}')
        ->methods(['GET'])
        ->controller([HomeController::class, 'index'])
        ->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])
        ->options(['auth' => false]);

    // User login page
    $routes->add('user.login', '/{locale}/user/login')
        ->methods(['GET'])
        ->controller([UserController::class, 'login'])
        ->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])
        ->options(['auth' => false]);
};
```

### REST Routes (Request Handlers)

For API endpoints. Define in `router/rest.php`:

```php
<?php declare(strict_types=1);

use App\User\Http\Request\LoginRequest;
use Megio\Translation\Resolver\PosixResolver;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('api.user.login', '/api/v1/{locale}/user/login')
        ->methods(['POST'])
        ->controller(LoginRequest::class)
        ->options(['auth' => false])
        ->requirements(['locale' => PosixResolver::LOCALE_POSIX_PATTERN]);
};
```

## Route Configuration

### Route Name

Unique identifier used for URL generation:

```php
$routes->add('user.login', '/user/login')
```

### HTTP Methods

```php
->methods(['GET'])           // Single method
->methods(['GET', 'POST'])   // Multiple methods
->methods(['POST'])          // API endpoints typically POST
```

### Controller

For web routes (Latte rendering):

```php
->controller([UserController::class, 'login'])
```

For REST routes (Request handlers):

```php
->controller(LoginRequest::class)
```

### Route Parameters

```php
// Required parameter
$routes->add('user.profile', '/user/{id}')

// Optional parameter with default
$routes->add('blog.list', '/blog/{page}')
    ->defaults(['page' => 1])

// Parameter validation
$routes->add('user.activation', '/{locale}/user/activate/{token}')
    ->requirements([
        'locale' => PosixResolver::LOCALE_SHORT_PATTERN,
        'token' => '[a-zA-Z0-9]+',
    ])
```

### Authentication Options

```php
// Public endpoint (no auth required)
->options(['auth' => false])

// Protected endpoint (default, requires JWT)
->options(['auth' => true])
// or simply omit options (auth: true is default)

// Not in permission resources
->options(['inResources' => false])
```

## Locale Patterns

Use `PosixResolver` constants for locale validation:

| Pattern | Constant | Example | Usage |
|---------|----------|---------|-------|
| Short | `LOCALE_SHORT_PATTERN` | `cs`, `en` | Web URLs |
| POSIX | `LOCALE_POSIX_PATTERN` | `cs_CZ`, `en_US` | API URLs |

```php
use Megio\Translation\Resolver\PosixResolver;

// Web route: /cs/dashboard
->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])

// API route: /api/v1/cs_CZ/user/login
->requirements(['locale' => PosixResolver::LOCALE_POSIX_PATTERN])
```

## Route Collections (Grouping)

**Not recommended.** Prefer flat route definitions for better readability.

Route collections exist but make code harder to navigate:

```php
// NOT RECOMMENDED - harder to read
$auth = $routes->collection('api.auth.')->prefix('/api/v1/auth');
$auth->add('login', '/login')->controller(LoginRequest::class);

// RECOMMENDED - explicit full paths
$routes->add('api.auth.login', '/api/v1/{locale}/auth/login')
    ->methods(['POST'])
    ->controller(LoginRequest::class)
    ->options(['auth' => false]);

$routes->add('api.auth.logout', '/api/v1/{locale}/auth/logout')
    ->methods(['POST'])
    ->controller(LogoutRequest::class);
```

Flat definitions are easier to search, understand, and debug.

## Generating URLs

### In Controllers/Requests

Use `LinkResolver` for URL generation:

```php
use Megio\Http\Resolver\LinkResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class UserRegistrationMailer
{
    public function __construct(
        private LinkResolver $linkResolver,
    ) {}

    public function send(User $user): void
    {
        // Relative path: /cs/user/activate/abc123
        $link = $this->linkResolver->link('user.activation', [
            'locale' => 'cs',
            'token' => $user->getActivationToken(),
        ]);

        // Absolute URL: https://example.com/cs/user/activate/abc123
        $absoluteLink = $this->linkResolver->link('user.activation', [
            'locale' => 'cs',
            'token' => $user->getActivationToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
```

### In Latte Templates

```latte
{* Using route name *}
<a href="{route('user.login', ['locale' => $translator->getShortCode()])}">{_'nav.login'}</a>

{* With parameters *}
<a href="{route('user.profile', ['locale' => $locale, 'id' => $user->getId()])}">{_'nav.profile'}</a>
```

## Framework Routes

The framework provides built-in routes in `vendor/strategio/megio-core/router/app.php`:

| Route | Path | Description |
|-------|------|-------------|
| `megio.app` | `/app{uri}` | Admin panel SPA |
| `megio.api` | `/api` | API overview |
| `megio.auth.*` | `/megio/auth/*` | Authentication |
| `megio.admin.*` | `/megio/admin/*` | Admin profile |
| `megio.collection.*` | `/megio/collections/*` | CRUD operations |
| `megio.resources.*` | `/megio/resources/*` | Permissions |
| `megio.translation.*` | `/megio/translation/*` | Translations |

## How Router Loads Routes

The main `router/app.php` imports all route files:

```php
<?php declare(strict_types=1);

use Megio\Helper\Path;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    // Your REST API routes
    $routes->import(Path::routerDir() . '/rest.php')->stateless();

    // Your web routes
    $routes->import(Path::routerDir() . '/web.php')->stateless();

    // Framework routes (admin panel, collections, auth)
    $routes->import(Path::megioVendorDir() . '/router/app.php')->stateless();
};
```

## Routing Rules

### Route Naming Convention

- **Web routes:** `{domain}.{action}` (e.g., `user.login`, `dashboard.index`)
- **API routes:** `api.{domain}.{action}` (e.g., `api.user.login`, `api.user.register`)
- Use dots as separators, lowercase

### Where to Define Routes

| Route Type | File | Controller Type |
|------------|------|-----------------|
| Web pages | `router/web.php` | Controller (renders Latte) |
| REST API | `router/rest.php` | Request handler (returns JSON) |

### Required Patterns

- Always use `PosixResolver` constants for locale validation
- Always specify HTTP methods explicitly
- Always set `auth: false` for public endpoints
- Prefer flat route definitions (avoid grouping)
- Use meaningful route names for URL generation
