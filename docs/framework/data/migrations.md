---
layout: 'page'
uri: '/data/migrations'
position: 3
slug: 'data-migrations'
parent: 'data'
navTitle: 'Migrations'
title: 'Database Migrations'
description: 'Doctrine migrations for version-controlled database schema changes.'
---

# Database Migrations

Doctrine migrations for version-controlled database schema changes.

## Directory Structure

```
migrations/
└── Version20251202055530.php
```

## Migration Workflow

### 1. Modify Entity

Add or change properties in your entity:

```php
// app/Product/Database/Entity/Product.php

#[ORM\Column(type: 'text', nullable: true)]
private ?string $description = null;

#[ORM\Column(options: ['default' => 0])]
private int $stockQuantity = 0;
```

### 2. Generate Migration

```bash
docker compose exec app bin/console migration:diff --no-interaction
```

This creates a new migration file in `migrations/` with SQL statements.

### 3. Review Migration

Check the generated file:

```php
<?php
declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202060000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add description and stockQuantity to product';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD stock_quantity INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP description');
        $this->addSql('ALTER TABLE product DROP stock_quantity');
    }
}
```

### 4. Run Migration

```bash
docker compose exec app bin/console migration:migrate --no-interaction
```

## CLI Commands

### Generate Migration from Entity Changes

```bash
docker compose exec app bin/console migration:diff --no-interaction
```

Compares entity definitions with current database schema and generates SQL.

### Run Pending Migrations

```bash
docker compose exec app bin/console migration:migrate --no-interaction
```

Executes all pending migrations.

### Check Migration Status

```bash
docker compose exec app bin/console migration:status
```

Shows which migrations have been executed.

### List Migrations

```bash
docker compose exec app bin/console migration:list
```

Lists all available migrations.

### Rollback Last Migration

```bash
docker compose exec app bin/console migration:migrate prev
```

Rolls back the most recent migration.

## Development Workflow

### On `make serve`

Migrations run automatically:

```makefile
serve:
    docker compose up -d
    docker compose exec app bin/console migration:migrate --no-interaction
    # ... other commands
```

### Typical Development Cycle

1. **Create/modify entity** in `app/{Domain}/Database/Entity/`
2. **Generate migration:** `docker compose exec app bin/console migration:diff`
3. **Review generated SQL** in `migrations/`
4. **Run migration:** `docker compose exec app bin/console migration:migrate`
5. **Update EntityManager** if new entity
6. **Test with `make test`**

## Migration File Structure

```php
<?php
declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202055530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Brief description of changes';
    }

    public function up(Schema $schema): void
    {
        // SQL for upgrading schema
        $this->addSql('CREATE TABLE ...');
        $this->addSql('ALTER TABLE ...');
    }

    public function down(Schema $schema): void
    {
        // SQL for reverting changes
        $this->addSql('DROP TABLE ...');
        $this->addSql('ALTER TABLE ...');
    }
}
```

## Common SQL Operations

### Create Table

```php
$this->addSql('CREATE TABLE "product" (
    id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY (id)
)');
```

### Add Column

```php
$this->addSql('ALTER TABLE product ADD description TEXT DEFAULT NULL');
$this->addSql('ALTER TABLE product ADD stock_quantity INT DEFAULT 0 NOT NULL');
```

### Add Index

```php
$this->addSql('CREATE INDEX IDX_product_name ON product (name)');
$this->addSql('CREATE UNIQUE INDEX UNIQ_product_code ON product (code)');
```

### Add Foreign Key

```php
$this->addSql('ALTER TABLE product
    ADD CONSTRAINT FK_product_category
    FOREIGN KEY (category_id)
    REFERENCES category (id)
    ON DELETE SET NULL');
```

### Drop Table

```php
$this->addSql('DROP TABLE product');
```

## Data Migrations

For data transformations, use SQL in migrations:

```php
public function up(Schema $schema): void
{
    // Schema change
    $this->addSql('ALTER TABLE product ADD status VARCHAR(20) DEFAULT \'active\' NOT NULL');

    // Data migration
    $this->addSql('UPDATE product SET status = \'inactive\' WHERE is_active = false');

    // Remove old column
    $this->addSql('ALTER TABLE product DROP is_active');
}
```

## Migration Rules

### Required Patterns

- Always review generated migrations before running
- Add meaningful description in `getDescription()`
- Implement both `up()` and `down()` methods
- Use `--no-interaction` flag in CI/CD
- Run `make test` after migrations

### Forbidden

- Manual schema changes without migrations
- Modifying executed migrations
- Skipping migrations
- Running migrations without review

### Best Practices

- One logical change per migration
- Keep migrations small and focused
- Test rollback (`down()`) method
- Use transactions for data migrations

## Troubleshooting

### Schema Out of Sync

If entities don't match database:

```bash
# Validate schema
docker compose exec app bin/console orm:validate-schema

# Force regenerate proxies
docker compose exec app bin/console orm:generate-proxies
```

### Migration Conflicts

If migration fails:

1. Check migration status: `migration:status`
2. Fix the issue manually if needed
3. Mark as executed: `migration:version --add VERSION_NUMBER`
