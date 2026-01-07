---
layout: 'page'
uri: '/data/entities'
position: 1
slug: 'data-entities'
parent: 'data'
navTitle: 'Entities'
title: 'Database Entities'
description: 'Doctrine ORM entities with UUIDv7, field traits, and lifecycle callbacks.'
---

# Database Entities

Doctrine ORM entities with UUIDv7, field traits, and lifecycle callbacks.

## Directory Structure

```
app/DomainName/
└── Database/
    └── Entity/
        └── EntityName.php
```

## Creating an Entity

### Basic Entity

```php
<?php
declare(strict_types=1);

namespace App\Product\Database\Entity;

use App\Product\Database\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Megio\Database\Field\TCreatedAt;
use Megio\Database\Field\TId;
use Megio\Database\Field\TUpdatedAt;
use Megio\Database\Interface\ICrudable;

#[ORM\Table(name: '`product`')]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product implements ICrudable
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    // Getters and setters...
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    // ... more getters/setters
}
```

## Required Components

### ORM Attributes

```php
// Table name (use backticks for reserved words)
#[ORM\Table(name: '`product`')]

// Repository class reference
#[ORM\Entity(repositoryClass: ProductRepository::class)]

// Enable lifecycle callbacks (for TCreatedAt, TUpdatedAt)
#[ORM\HasLifecycleCallbacks]
```

### Interfaces

| Interface | Purpose | Required for |
|-----------|---------|--------------|
| `ICrudable` | Base CRUD interface | Admin panel Recipe |
| `IAuthenticable` | Authentication | User/Admin entities |
| `IJoinable` | Relationship labels | Entity select fields |

```php
// For admin panel CRUD
class Product implements ICrudable

// For user authentication
class User implements ICrudable, IAuthenticable, IJoinable
```

### IJoinable Implementation

For entities used in relationship selects:

```php
public function getJoinableLabel(): array
{
    return [
        'fields' => ['name', 'email'],
        'format' => '%s (%s)',
    ];
}
```

## Field Traits

Reusable field definitions from `Megio\Database\Field\`:

| Trait | Fields | Description |
|-------|--------|-------------|
| `TId` | `$id` | UUIDv7 primary key |
| `TCreatedAt` | `$createdAt` | Auto-set on persist |
| `TUpdatedAt` | `$updatedAt` | Auto-set on update |
| `TEmail` | `$email` | Email with unique constraint |
| `TPassword` | `$password` | Argon2ID hashed password |
| `TLastLogin` | `$lastLogin` | Last login timestamp |

### TId (Always Use)

```php
use Megio\Database\Field\TId;

class Product implements ICrudable
{
    use TId;
    // Provides: getId(): string
    // Auto-generates UUIDv7 on persist
}
```

### TCreatedAt and TUpdatedAt

```php
use Megio\Database\Field\TCreatedAt;
use Megio\Database\Field\TUpdatedAt;

#[ORM\HasLifecycleCallbacks]  // Required!
class Product implements ICrudable
{
    use TCreatedAt;  // Auto-set on insert
    use TUpdatedAt;  // Auto-set on update

    // Provides: getCreatedAt(): DateTime
    // Provides: getUpdatedAt(): DateTime
}
```

## Column Types

### Common Column Definitions

```php
// String (default 255 chars)
#[ORM\Column]
private string $name;

// String with length
#[ORM\Column(length: 100)]
private string $code;

// Text (unlimited)
#[ORM\Column(type: 'text')]
private string $content;

// Nullable text
#[ORM\Column(type: 'text', nullable: true)]
private ?string $description = null;

// Boolean with default
#[ORM\Column(options: ['default' => true])]
private bool $isActive = true;

// Integer
#[ORM\Column(type: 'integer')]
private int $quantity;

// Decimal (price)
#[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
private string $price;

// DateTime
#[ORM\Column(nullable: true)]
private ?DateTime $publishedAt = null;

// JSON
#[ORM\Column(type: 'json')]
private array $metadata = [];

// Enum
#[ORM\Column(type: 'string', enumType: ProductStatus::class)]
private ProductStatus $status;
```

## Relationships

### ManyToOne

```php
#[ORM\ManyToOne(targetEntity: Category::class)]
#[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
private ?Category $category = null;
```

### OneToMany

```php
/** @var Collection<int, OrderItem> */
#[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist', 'remove'])]
private Collection $items;

public function __construct()
{
    $this->items = new ArrayCollection();
}
```

### ManyToMany

```php
/** @var Collection<int, Role> */
#[ORM\ManyToMany(targetEntity: Role::class)]
#[ORM\JoinTable(name: 'user_has_role')]
#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
#[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
private Collection $roles;

public function __construct()
{
    $this->roles = new ArrayCollection();
}

public function addRole(Role $role): void
{
    if ($this->roles->contains($role) === false) {
        $this->roles->add($role);
    }
}

public function removeRole(Role $role): void
{
    $this->roles->removeElement($role);
}
```

## Indexes

```php
#[ORM\Table(name: '`product`')]
#[ORM\Index(fields: ['status', 'isActive'])]
#[ORM\UniqueConstraint(fields: ['code'])]
class Product implements ICrudable
```

## Register in EntityManager

Add getter method to `app/EntityManager.php`:

```php
use App\Product\Database\Entity\Product;
use App\Product\Database\Repository\ProductRepository;

public function getProductRepo(): ProductRepository
{
    $repo = $this->getRepository(Product::class);
    assert(($repo instanceof ProductRepository) === true);
    return $repo;
}
```

## Entity Rules

### Required Patterns

- Always use `TId` trait (UUIDv7)
- Always use `TCreatedAt` and `TUpdatedAt`
- Always add `#[ORM\HasLifecycleCallbacks]`
- Implement `ICrudable` for admin panel
- Use backticks for table names
- Private properties with public getters/setters
- Collection properties initialized in constructor

### Forbidden

- Integer IDs (always use UUIDv7 string)
- Public properties
- Business logic in entities
- Database queries in entities
- Direct EntityManager usage

### Naming Conventions

- Entity class: `PascalCase` singular (`Product`, `User`)
- Table name: `snake_case` singular (`product`, `user`)
- Column name: `camelCase` (`isActive`, `createdAt`)
- Join table: `{entity}_has_{related}` (`user_has_role`)
