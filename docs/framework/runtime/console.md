---
layout: 'page'
uri: '/runtime/console'
position: 5
slug: 'runtime-console'
parent: 'runtime'
navTitle: 'Console Commands'
title: 'Console Commands'
description: 'Symfony Console commands for CLI operations like user management, migrations, and queue workers.'
---

# Console Commands

Symfony Console commands for CLI operations like user management, migrations, and queue workers.

## How It Works

1. **Command class** extends Symfony's `Command`
2. **#[AsCommand]** attribute defines name and description
3. **DI** injects dependencies via constructor
4. **Execute** method contains command logic
5. **Output** displays results to console

## Directory Structure

```
app/DomainName/
└── Console/
    └── DomainCommand.php

bin/
└── console    # Entry point
```

## Running Commands

```bash
# Via Docker
docker compose exec app bin/console {command}

# List all commands
docker compose exec app bin/console list

# Get help for command
docker compose exec app bin/console {command} --help
```

## Creating a Command

### 1. Create Command class

```php
<?php
declare(strict_types=1);

namespace App\Product\Console;

use App\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function is_string;

#[AsCommand(
    name: 'app:product:import',
    description: 'Import products from CSV file.',
    aliases: ['product:import'],
)]
class ProductImportCommand extends Command
{
    public function __construct(
        private readonly EntityManager $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to CSV file');
        $this->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Run without saving');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $file = $input->getArgument('file');
        $dryRun = $input->getOption('dry-run');

        assert(is_string($file) === true);
        assert(is_bool($dryRun) === true);

        $output->writeln('<info>Importing products...</info>');

        // Import logic here...

        if ($dryRun === true) {
            $output->writeln('<comment>Dry run - no changes saved.</comment>');
        } else {
            $this->em->flush();
            $output->writeln('<info>Import completed.</info>');
        }

        return Command::SUCCESS;
    }
}
```

### 2. Register in DI

Add to `app/{Domain}/{domain}.neon`:

```neon
services:
    - App\Product\Console\ProductImportCommand
```

## Built-in Commands

### User Management

```bash
# Create admin user
docker compose exec app bin/console app:user:create-admin admin@example.com password123

# Create regular user
docker compose exec app bin/console app:user:create user@example.com password123

# Create user with role
docker compose exec app bin/console app:user:create user@example.com password123 --role=admin
```

### Database

```bash
# Generate migration from entity changes
docker compose exec app bin/console migration:diff --no-interaction

# Run pending migrations
docker compose exec app bin/console migration:migrate --no-interaction

# Check migration status
docker compose exec app bin/console migration:status

# Validate schema
docker compose exec app bin/console orm:validate-schema

# Generate proxies
docker compose exec app bin/console orm:generate-proxies
```

### Queue

```bash
# Run queue worker
docker compose exec app bin/console queue:worker

# Run specific worker
docker compose exec app bin/console queue:worker --worker=email
```

### Permissions

```bash
# Update permissions from code
docker compose exec app bin/console app:permissions:update
```

### Translations

```bash
# Import translations from locale files
docker compose exec app bin/console translation:import

# Export translations to JSON
docker compose exec app bin/console translation:export
```

## Input Arguments

### Required Argument

```php
$this->addArgument('email', InputArgument::REQUIRED, 'User email');

// Usage: bin/console command user@example.com
$email = $input->getArgument('email');
```

### Optional Argument

```php
$this->addArgument('name', InputArgument::OPTIONAL, 'User name', 'default');

// Usage: bin/console command [name]
$name = $input->getArgument('name');
```

### Array Argument

```php
$this->addArgument('files', InputArgument::IS_ARRAY, 'Files to process');

// Usage: bin/console command file1.csv file2.csv
$files = $input->getArgument('files');
```

## Input Options

### Boolean Flag

```php
$this->addOption('verbose', 'v', InputOption::VALUE_NONE, 'Verbose output');

// Usage: bin/console command --verbose or -v
$verbose = $input->getOption('verbose'); // bool
```

### Option with Value

```php
$this->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format', 'json');

// Usage: bin/console command --format=csv
$format = $input->getOption('format'); // string
```

### Optional Value

```php
$this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit results', '100');

// Usage: bin/console command --limit=50
$limit = $input->getOption('limit'); // string|null
```

## Output Formatting

### Text Styles

```php
$output->writeln('<info>Success message</info>');      // Green
$output->writeln('<comment>Warning message</comment>'); // Yellow
$output->writeln('<error>Error message</error>');       // Red on white
$output->writeln('<question>Question</question>');     // Black on cyan
```

### Progress Bar

```php
use Symfony\Component\Console\Helper\ProgressBar;

$progressBar = new ProgressBar($output, count($items));
$progressBar->start();

foreach ($items as $item) {
    // Process item...
    $progressBar->advance();
}

$progressBar->finish();
$output->writeln('');
```

### Table Output

```php
use Symfony\Component\Console\Helper\Table;

$table = new Table($output);
$table
    ->setHeaders(['ID', 'Name', 'Email'])
    ->setRows([
        ['1', 'John', 'john@example.com'],
        ['2', 'Jane', 'jane@example.com'],
    ]);
$table->render();
```

## Return Codes

```php
return Command::SUCCESS;   // 0 - success
return Command::FAILURE;   // 1 - failure
return Command::INVALID;   // 2 - invalid input
```

## Command Rules

### Required Patterns

- Use `#[AsCommand]` attribute
- Extend `Command` class
- Call `parent::__construct()` in constructor
- Use `assert()` for argument types
- Return proper exit codes
- Register in domain `.neon` file

### Naming Convention

```
app:{domain}:{action}

# Examples:
app:user:create
app:product:import
app:order:process
```

### Forbidden

- Business logic in commands (use Facade)
- Direct database queries (use Repository)
- Interactive prompts in automated scripts
- Long-running processes without progress indication
