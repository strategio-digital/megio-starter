---
layout: 'page'
uri: '/http/controllers'
position: 2
slug: 'http-controllers'
parent: 'http'
navTitle: 'Controllers'
title: 'Controllers'
description: 'Web controllers for rendering Latte templates with Vue.js integration.'
---

# Controllers

Web controllers for rendering Latte templates with Vue.js integration.

## How It Works

1. **Router** matches URL to Controller method
2. **DI container** instantiates Controller with dependencies
3. **Controller** calls `render()` with template path and data
4. **Latte** renders HTML with passed variables
5. **Vue** mounts components into placeholder divs

## Directory Structure

```
app/DomainName/
└── Http/
    └── Controller/
        └── DomainController.php

view/
├── @layout.latte              # Base layout
└── domain/
    └── controller/
        └── action.latte       # Page template
```

## Creating a Controller

### 1. Create Controller class

Place in `app/{Domain}/Http/Controller/` as `final` class:

```php
<?php
declare(strict_types=1);

namespace App\Dashboard\Http\Controller;

use Megio\Helper\Path;
use Megio\Http\Controller\Base\Controller;
use Megio\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly Translator $translator,
    ) {}

    public function dashboard(): Response
    {
        return $this->render(Path::viewDir() . '/dashboard/controller/dashboard.latte', [
            'title' => $this->translator->translate('dashboard.page.title'),
            'description' => $this->translator->translate('dashboard.page.description'),
        ]);
    }
}
```

### 2. Create Latte template

Place in `view/{domain}/controller/{action}.latte`:

```latte
{varType string $description}

{extends ./../../@layout.latte}

{block head}
    {include parent}
    <meta name="description" content="{$description}">
{/block}

{block content}
    <div id="vue-dashboard"></div>
{/block}
```

### 3. Register route

Add to `router/web.php`:

```php
$routes->add('dashboard', '/{locale}/dashboard')
    ->methods(['GET'])
    ->controller([DashboardController::class, 'dashboard'])
    ->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])
    ->options(['auth' => false]);
```

## Controller Methods

The base `Controller` class provides these methods:

### render()

Renders Latte template and returns Response:

```php
public function render(
    string $path,
    array $params = [],
    int $status = 200,
    array $headers = ['content-type' => 'text/html'],
): Response
```

Example:

```php
return $this->render(Path::viewDir() . '/user/controller/login.latte', [
    'title' => $this->translator->translate('user.page.login.title'),
    'description' => $this->translator->translate('user.page.login.description'),
]);
```

### redirect()

Redirects to named route:

```php
public function redirect(
    string $route,
    array $params = [],
    int $status = 302,
    array $headers = [],
): RedirectResponse
```

Example:

```php
return $this->redirect('dashboard', ['locale' => 'cs']);
```

### redirectUrl()

Redirects to absolute URL:

```php
return $this->redirectUrl('https://example.com');
```

### json()

Returns JSON response (use for AJAX in Controllers):

```php
return $this->json(['status' => 'ok']);
```

### sendFile()

Sends file for download:

```php
return $this->sendFile(new File('/path/to/file.pdf'));
```

### sendFileContent()

Sends string content as file download:

```php
return $this->sendFileContent($csvContent, 'export.csv');
```

## Route Parameters

Route parameters are passed as method arguments:

```php
// Route: /{locale}/user/activate/{token}
public function activate(string $token): Response
{
    return $this->render(Path::viewDir() . '/user/controller/activate.latte', [
        'title' => $this->translator->translate('user.page.activation.title'),
        'token' => $token,
    ]);
}
```

## Latte Templates

For complete Latte template documentation, see **[Latte Templates](/presentation/latte)**.

Templates are placed in `view/{domain}/controller/{action}.latte`.

## Vue.js Integration

### Mount Point

Create placeholder div with unique ID:

```latte
<div id="vue-dashboard"></div>
```

### Passing Data to Vue

Use `data-*` attributes:

```latte
<div id="vue-user-profile" data-user-id="{$userId}"></div>
```

Read in Vue:

```typescript
const el = document.getElementById('vue-user-profile');
if (el) {
    const userId = String(el.getAttribute('data-user-id'));
    createApp(UserProfile, { userId }).mount(el);
}
```

### Registration in app.ts

```typescript
// assets/app.ts
const loginEl = document.getElementById('vue-user-login-form');
if (loginEl) {
    const component = await import('@/assets/app/User/LoginForm.vue');
    createApp(component.default).mount(loginEl);
}
```

## Controller Rules

### Responsibility (SRP)

- **ONLY** render Latte templates
- **NO** business logic - use Facade
- **NO** database queries - use Facade
- **NO** email sending - dispatch to queue via Facade

### Where Controllers Belong

| Task | Layer |
|------|-------|
| Render web page | Controller |
| Return JSON API | Request Handler |
| Business logic | Facade |
| Database queries | Repository |

### Required Patterns

- Use `final` class (no inheritance beyond Controller)
- Inject dependencies via constructor
- Use `Path::viewDir()` for template paths
- Pass `title` and `description` for SEO
- Use Vue placeholder divs for interactivity
- Translate in Controller, not in Latte (for dynamic content)

### Forbidden

- Direct EntityManager usage
- Business logic
- Email sending
- Queue dispatching
- File operations
