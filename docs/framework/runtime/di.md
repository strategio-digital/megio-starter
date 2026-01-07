---
layout: 'page'
uri: '/runtime/di'
position: 1
slug: 'runtime-di'
parent: 'runtime'
navTitle: 'Dependency Injection'
title: 'Dependency Injection'
description: 'Nette DI container with NEON configuration for service registration and autowiring.'
---

# Dependency Injection

Nette DI container with NEON configuration for service registration and autowiring.

## How It Works

1. **NEON files** define service registration
2. **Nette DI** compiles container at runtime
3. **Autowiring** resolves dependencies automatically
4. **Constructor injection** provides dependencies

## Directory Structure

```
app/
├── app.neon              # Main configuration
├── EntityManager.php     # Custom EntityManager
├── QueueWorker.php       # Queue worker enum
└── DomainName/
    └── domain.neon       # Domain services
```

## Configuration Files

### Main Configuration (app.neon)

```neon
services:
    # Override base EntityManager
    entityManager: App\EntityManager

    # Register queue workers
    - Megio\Queue\QueueWorkerEnumFactory(App\QueueWorker)

includes:
    # Include megio-core configs
    - ./../vendor/strategio/megio-core/config/app.neon
    - ./../vendor/strategio/megio-core/config/events.neon

    # Include domain configs
    - ./Dashboard/dashboard.neon
    - ./User/user.neon

events:
    # Global event subscribers
    #- Megio\Subscriber\RedirectToWww

extensions:
    doctrine: Megio\Extension\Doctrine\DoctrineExtension
    events: Megio\Extension\Events\EventsExtension
    latte: Megio\Extension\Latte\LatteExtension
    translation: Megio\Translation\Extension\TranslationExtension
```

### Domain Configuration (user.neon)

```neon
services:
    # Console commands
    - App\User\Console\UserCreateCommand
    - App\User\Console\UserRoleAssignCommand

    # Resolvers
    - App\User\Resolver\UserTokenResolver

    # Facades
    - App\User\Facade\UserAuthFacade

    # Mailers
    - App\User\Mail\UserRegistrationMailer
    - App\User\Mail\PasswordResetMailer

events:
    # Domain event subscribers
    - App\User\Subscriber\Kernel\DisableMegioUserLogin
```

## Registering Services

### Simple Registration

```neon
services:
    # Autowired by class name
    - App\Product\Facade\CreateProductFacade
    - App\Product\Facade\UpdateProductFacade
```

### Named Service

```neon
services:
    # Named service (accessible by name)
    productService: App\Product\Service\ProductService
```

### With Arguments

```neon
services:
    - App\Notification\Service\SlackNotifier('webhook-url')
```

### With Factory

```neon
services:
    - Megio\Queue\QueueWorkerEnumFactory(App\QueueWorker)
```

## Service Types

### Facades

```neon
services:
    - App\Order\Facade\CreateOrderFacade
    - App\Order\Facade\ProcessOrderFacade
    - App\Order\Facade\CancelOrderFacade
```

### Resolvers

```neon
services:
    - App\Order\Resolver\OrderPriceResolver
    - App\Order\Resolver\OrderStatusResolver
```

### Mailers

```neon
services:
    - App\Order\Mail\OrderConfirmationMailer
    - App\Order\Mail\OrderShippedMailer
```

### Console Commands

```neon
services:
    - App\Order\Console\ProcessPendingOrdersCommand
```

### HTTP Clients

```neon
services:
    - App\Payment\Http\Client\StripeClient
```

## Event Subscribers

### In events Section

```neon
events:
    - App\Order\Subscriber\OrderCreatedSubscriber
    - App\Order\Subscriber\OrderPaymentSubscriber
```

## Autowiring

### Constructor Injection

```php
<?php
declare(strict_types=1);

namespace App\Product\Facade;

use App\EntityManager;
use Megio\Storage\Storage;
use Tracy\ILogger;

final readonly class CreateProductFacade
{
    public function __construct(
        private EntityManager $em,
        private Storage $storage,
        private ILogger $logger,
    ) {}

    public function execute(CreateProductDto $dto): Product
    {
        // Dependencies are automatically injected
    }
}
```

### Interface Binding

When multiple implementations exist, specify in NEON:

```neon
services:
    # Bind interface to implementation
    App\Payment\PaymentGatewayInterface: App\Payment\Gateway\StripeGateway
```

## Extensions

### Doctrine Extension

```neon
extensions:
    doctrine: Megio\Extension\Doctrine\DoctrineExtension
```

Provides:
- EntityManager
- Repositories
- Migrations
- Database connection

### Events Extension

```neon
extensions:
    events: Megio\Extension\Events\EventsExtension
```

Provides:
- EventDispatcher
- Subscriber registration

### Latte Extension

```neon
extensions:
    latte: Megio\Extension\Latte\LatteExtension
```

Provides:
- Latte engine
- Custom filters and functions
- Template rendering

### Translation Extension

```neon
extensions:
    translation: Megio\Translation\Extension\TranslationExtension
```

Provides:
- Translator service
- Locale management
- ICU MessageFormat

## Accessing Services

### In Facades/Services

```php
// Autowired via constructor
public function __construct(
    private readonly EntityManager $em,
) {}
```

### In Tests (Feature)

```php
// In feature tests
$facade = $this->getService(CreateProductFacade::class);
```

## DI Rules

### Required Patterns

- Register all services in domain `.neon` files
- Use constructor injection exclusively
- Use `readonly` for injected dependencies
- One class per service (no service locators)

### File Organization

```
app/{Domain}/
├── {domain}.neon         # All domain services
├── Console/              # Console commands
├── Facade/               # Business logic facades
├── Http/
│   ├── Client/           # HTTP clients
│   └── Request/          # Request handlers
├── Mail/                 # Mailers
├── Resolver/             # Resolvers
├── Subscriber/           # Event subscribers
└── Worker/               # Queue workers
```

### Forbidden

- Service locator pattern
- Setter injection
- Direct container access
- Circular dependencies

### Naming in NEON

| Type | Pattern |
|------|---------|
| Class service | `- App\Domain\Type\ClassName` |
| Named service | `serviceName: App\Domain\Type\ClassName` |
| With args | `- App\Domain\Type\ClassName('arg')` |
| Interface bind | `InterfaceName: ImplementationClass` |
