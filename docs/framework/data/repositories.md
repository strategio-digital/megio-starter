---
layout: 'page'
uri: '/data/repositories'
position: 2
slug: 'data-repositories'
parent: 'data'
navTitle: 'Repositories'
title: 'Repositories'
description: 'Doctrine repositories for database queries with typed methods and EntityManager integration.'
---

# Repositories

Doctrine repositories for database queries with typed methods and EntityManager integration.

## Directory Structure

```
app/DomainName/
└── Database/
    └── Repository/
        └── EntityNameRepository.php

app/
└── EntityManager.php    # Central repository access
```

## Creating a Repository

### 1. Create Repository class

```php
<?php
declare(strict_types=1);

namespace App\Product\Database\Repository;

use App\Product\Database\Entity\Product;
use Doctrine\ORM\EntityRepository;

/**
 * @method Product|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Product|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Product[] findAll()
 * @method Product[] findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, ?int $limit = null, ?int $offset = null)
 *
 * @extends EntityRepository<ProductRepository>
 */
class ProductRepository extends EntityRepository {}
```

### 2. Register in EntityManager

Add getter to `app/EntityManager.php`:

```php
<?php
declare(strict_types=1);

namespace App;

use App\Product\Database\Entity\Product;
use App\Product\Database\Repository\ProductRepository;

use function assert;

class EntityManager extends \Megio\Database\EntityManager
{
    public function getProductRepo(): ProductRepository
    {
        $repo = $this->getRepository(Product::class);
        assert(($repo instanceof ProductRepository) === true);
        return $repo;
    }
}
```

## Using Repositories

### In Facades

```php
final readonly class CreateProductFacade
{
    public function __construct(
        private EntityManager $em,
    ) {}

    public function execute(CreateProductDto $dto): Product
    {
        // Find by ID
        $category = $this->em->getCategoryRepo()->find($dto->categoryId);

        // Find one by criteria
        $existing = $this->em->getProductRepo()->findOneBy([
            'code' => $dto->code,
        ]);

        // Find all
        $allProducts = $this->em->getProductRepo()->findAll();

        // Find by criteria with ordering and limit
        $recent = $this->em->getProductRepo()->findBy(
            criteria: ['isActive' => true],
            orderBy: ['createdAt' => 'DESC'],
            limit: 10,
        );

        // Create new entity
        $product = new Product();
        $product->setName($dto->name);

        // Persist and flush
        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }
}
```

## Built-in Repository Methods

All repositories inherit these from Doctrine:

| Method | Return | Description |
|--------|--------|-------------|
| `find($id)` | `Entity\|null` | Find by primary key |
| `findOneBy($criteria)` | `Entity\|null` | Find one by criteria |
| `findAll()` | `Entity[]` | Find all entities |
| `findBy($criteria, $orderBy, $limit, $offset)` | `Entity[]` | Find by criteria |

## Custom Query Methods

Add custom queries to repository:

```php
class ProductRepository extends EntityRepository
{
    /**
     * @return Product[]
     */
    public function findActiveByCategory(string $categoryId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.category = :categoryId')
            ->andWhere('p.isActive = :active')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('active', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByCategory(string $categoryId): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
```

## QueryBuilder Examples

### Basic Select

```php
$qb = $this->createQueryBuilder('p');

$products = $qb
    ->where('p.isActive = :active')
    ->setParameter('active', true)
    ->getQuery()
    ->getResult();
```

### With Joins

```php
$products = $this->createQueryBuilder('p')
    ->leftJoin('p.category', 'c')
    ->addSelect('c')
    ->where('c.name = :categoryName')
    ->setParameter('categoryName', 'Electronics')
    ->getQuery()
    ->getResult();
```

### Ordering and Limiting

```php
$products = $this->createQueryBuilder('p')
    ->orderBy('p.createdAt', 'DESC')
    ->setMaxResults(10)
    ->setFirstResult(0)  // offset for pagination
    ->getQuery()
    ->getResult();
```

### Aggregate Functions

```php
// Count
$count = $this->createQueryBuilder('p')
    ->select('COUNT(p.id)')
    ->getQuery()
    ->getSingleScalarResult();

// Sum
$total = $this->createQueryBuilder('o')
    ->select('SUM(o.total)')
    ->where('o.status = :status')
    ->setParameter('status', 'completed')
    ->getQuery()
    ->getSingleScalarResult();
```

### Dynamic Conditions

```php
public function search(array $filters): array
{
    $qb = $this->createQueryBuilder('p');

    if (array_key_exists('name', $filters) === true) {
        $qb->andWhere('p.name LIKE :name')
           ->setParameter('name', '%' . $filters['name'] . '%');
    }

    if (array_key_exists('minPrice', $filters) === true) {
        $qb->andWhere('p.price >= :minPrice')
           ->setParameter('minPrice', $filters['minPrice']);
    }

    if (array_key_exists('categoryId', $filters) === true) {
        $qb->andWhere('p.category = :categoryId')
           ->setParameter('categoryId', $filters['categoryId']);
    }

    return $qb->getQuery()->getResult();
}
```

## EntityManager Operations

### Persist and Flush

```php
// Create
$entity = new Product();
$entity->setName('New Product');
$this->em->persist($entity);
$this->em->flush();

// Update (just flush after changes)
$entity->setName('Updated Name');
$this->em->flush();
```

### Remove

```php
$this->em->remove($entity);
$this->em->flush();
```

### Transaction

```php
$this->em->wrapInTransaction(function () use ($dto) {
    $order = new Order();
    $this->em->persist($order);

    foreach ($dto->items as $item) {
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);
        $this->em->persist($orderItem);
    }
});
```

## Repository Rules

### Required Patterns

- Extend `EntityRepository`
- Add PHPDoc with typed method annotations
- Register in `app/EntityManager.php`
- Use `assert()` for type safety in EntityManager
- Return typed arrays or entities (never raw arrays)

### Forbidden

- Business logic in repositories
- Email sending
- Queue dispatching
- Direct SQL queries (use QueryBuilder)
- Returning raw arrays from queries

### Access Pattern

```
Facade → EntityManager → Repository → Entity
         ↑
         Always inject EntityManager, not Repository directly
```

Repositories are accessed through `EntityManager`:

```php
// CORRECT
$this->em->getProductRepo()->find($id);

// WRONG - don't inject repositories directly
public function __construct(private ProductRepository $repo) {}
```
