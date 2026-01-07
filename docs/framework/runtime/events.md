---
layout: 'page'
uri: '/runtime/events'
position: 2
slug: 'runtime-events'
parent: 'runtime'
navTitle: 'Events & Subscribers'
title: 'Events & Subscribers'
description: 'Symfony event system with Kernel, Request, Collection, and Doctrine lifecycle events.'
---

# Events & Subscribers

Symfony event system with Kernel, Request, Collection, and Doctrine lifecycle events.

## How It Works

1. **Event** is dispatched at specific point in application lifecycle
2. **Subscriber** listens for events via `EventSubscriberInterface`
3. **Handler** method processes the event and can modify data or response
4. **Propagation** can be stopped to prevent further processing

## Directory Structure

```
app/DomainName/
└── Subscriber/
    └── DomainSubscriber.php

vendor/strategio/megio-core/src/
├── Event/
│   ├── Request/           # Request lifecycle events
│   └── Collection/        # CRUD operation events
└── Subscriber/            # Built-in subscribers
```

## Event Types

### Symfony Kernel Events

Standard HTTP lifecycle events:

| Event | When | Use Case |
|-------|------|----------|
| `KernelEvents::REQUEST` | Before controller | Auth, CORS, redirects |
| `KernelEvents::RESPONSE` | After controller | Add headers, modify response |
| `KernelEvents::EXCEPTION` | On exception | Error handling, logging |

### Request Events

Custom events for Request Handlers:

| Event | Class | When |
|-------|-------|------|
| `BEFORE_VALIDATION` | `BeforeValidationEvent` | Before DTO validation |
| `ON_VALIDATION_EXCEPTION` | `OnValidationExceptionEvent` | When validation fails |
| `BEFORE_PROCESSING_DATA` | `BeforeProcessEvent` | Before `process()` method |
| `AFTER_PROCESSING_DATA` | `AfterProcessEvent` | After `process()` method |

### Collection Events

Events for admin panel CRUD operations:

| Event | Class | When |
|-------|-------|------|
| `ON_START` | `OnStartEvent` | Before CRUD operation |
| `ON_EXCEPTION` | `OnExceptionEvent` | When operation fails |
| `ON_FINISH` | `OnFinishEvent` | After operation completes |
| `ON_FORM_START` | `OnFormStartEvent` | Before form rendering |

### Doctrine Lifecycle Events

Entity lifecycle callbacks:

| Event | When |
|-------|------|
| `#[ORM\PrePersist]` | Before first insert |
| `#[ORM\PostPersist]` | After first insert |
| `#[ORM\PreUpdate]` | Before update |
| `#[ORM\PostUpdate]` | After update |
| `#[ORM\PreRemove]` | Before delete |
| `#[ORM\PostRemove]` | After delete |

## Creating a Subscriber

### 1. Create Subscriber class

```php
<?php
declare(strict_types=1);

namespace App\Order\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class OrderRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 100],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest() === false) {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if ($routeName === null) {
            return;
        }

        // Your logic here
    }
}
```

### 2. Register in DI

Add to `app/{Domain}/{domain}.neon`:

```neon
services:
    - App\Order\Subscriber\OrderRequestSubscriber
```

## Event Priority

Higher priority = executed first:

```php
public static function getSubscribedEvents(): array
{
    return [
        KernelEvents::REQUEST => [
            ['onRequestFirst', 9999],   // Highest priority
            ['onRequestSecond', 100],   // Normal priority
            ['onRequestLast', -100],    // Low priority
        ],
    ];
}
```

## Stopping Propagation

Prevent other subscribers from processing:

```php
public function onRequest(RequestEvent $event): void
{
    if ($this->shouldBlock() === true) {
        $event->setResponse(new JsonResponse(['error' => 'Blocked'], 403));
        $event->stopPropagation();
    }
}
```

## Request Event Examples

### Auth Subscriber

```php
<?php
declare(strict_types=1);

namespace App\User\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class AuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RouteCollection $routes,
        private readonly JWTResolver $jwt,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest'],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if ($routeName === null) {
            return;
        }

        /** @var Route $currentRoute */
        $currentRoute = $this->routes->get($routeName);

        // Skip auth for public routes
        if ($currentRoute->getOption('auth') === false) {
            return;
        }

        $authHeader = $request->headers->get('Authorization');

        if (is_string($authHeader) === false) {
            $event->setResponse(new JsonResponse(['errors' => ['Missing Authorization header']], 401));
            $event->stopPropagation();
            return;
        }

        // Validate JWT token...
    }
}
```

### Response Header Subscriber

```php
<?php
declare(strict_types=1);

namespace App\Core\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onResponse'],
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        if ($event->isMainRequest() === false) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
    }
}
```

## Collection Event Examples

### Audit Subscriber

```php
<?php
declare(strict_types=1);

namespace App\Audit\Subscriber;

use Megio\Event\Collection\Events;
use Megio\Event\Collection\OnFinishEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AuditCollectionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ON_FINISH->value => ['onFinish'],
        ];
    }

    public function onFinish(OnFinishEvent $event): void
    {
        $this->auditLogger->log(
            action: $event->getEventType()->name,
            recipe: $event->getRecipe()->key(),
            data: $event->getData(),
        );
    }
}
```

## Doctrine Lifecycle Events

### In Entity

Use `#[ORM\HasLifecycleCallbacks]` on entity class:

```php
<?php
declare(strict_types=1);

namespace App\Product\Database\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
```

### Using Traits

Megio provides ready-to-use traits:

```php
use Megio\Database\Field\TCreatedAt;
use Megio\Database\Field\TUpdatedAt;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Product
{
    use TCreatedAt;  // Adds createdAt with #[ORM\PrePersist]
    use TUpdatedAt;  // Adds updatedAt with #[ORM\PrePersist] and #[ORM\PreUpdate]
}
```

## Built-in Subscribers

Megio provides these subscribers:

| Subscriber | Purpose |
|------------|---------|
| `AuthRequest` | JWT authentication |
| `AuthRouteRequest` | Route-based auth |
| `AuthCollectionRequest` | Collection CRUD auth |
| `AuthCollectionFormRequest` | Collection form auth |
| `CorsRequest` | CORS headers |
| `CSPResponse` | Content Security Policy |
| `RedirectToWww` | www redirect |

## Event Rules

### Required Patterns

- Implement `EventSubscriberInterface`
- Use `final` class
- Inject dependencies via constructor
- Check `isMainRequest()` for kernel events
- Check `$routeName === null` before route operations
- Use explicit boolean checks (`=== true`, `=== false`)

### Forbidden

- Business logic in subscribers (use Facade)
- Database queries beyond auth checks
- Email sending (dispatch to queue)
- Heavy processing (blocking request)

### Where to Use

| Use Case | Approach |
|----------|----------|
| Authentication | Kernel REQUEST subscriber |
| CORS | Kernel REQUEST + RESPONSE subscriber |
| Logging | Collection ON_FINISH subscriber |
| Timestamps | Doctrine #[ORM\PrePersist] |
| Validation modification | Request BEFORE_VALIDATION subscriber |
