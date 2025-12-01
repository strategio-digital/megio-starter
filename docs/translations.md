---
layout: 'page'
uri: '/translations'
position: 2
slug: 'translations'
navTitle: 'Translations'
title: 'Translations'
description: 'Multi-language support with .neon files, database storage, and ICU MessageFormat.'
---

# Translations

Multi-language support with .neon files, database storage, and ICU MessageFormat.

## How It Works

1. **Default language** is stored in `.neon` files in `locale/` directory
2. **Other languages** are stored in database (can be managed via ~~UI~~ or API)
3. **Import command** syncs `.neon` files to database
4. **Caching** - translations are cached, invalidated on import

## Configuration

Add environment variables to `.env`:

```dotenv
TRANSLATIONS_DEFAULT_LOCALE=en_US
TRANSLATIONS_FALLBACK_LOCALES=en_US
TRANSLATIONS_ENABLE_CACHE=true
```

## Creating Translation Files

### 1. Create a locale file

Place translation files in `locale/` directory:

```
locale/{domain}.locale.{LOCALE_CODE}.neon
```

Example: `locale/app.locale.en_US.neon`, `locale/user.locale.en_US.neon`

### 2. Add translations

Use NEON format with nested structure (automatically flattened to dot notation).

File `user.locale.en_US.neon`:

```neon
form:
    email:
        label: 'Email'
        placeholder: 'Enter your email'
```

Becomes keys: `user.form.email.label`, `user.form.email.placeholder`

The first key segment is the domain name from the filename (before `.locale.`).

### 3. Import to database

```bash
bin/console translation:import
```

## Using ICU MessageFormat

Supports plurals and variables:

```neon
items: '{count, plural, =0 {No items} =1 {One item} other {# items}}'
greeting: 'Hello {name}!'
```

## Using in PHP

Inject `Megio\Translation\Translator`:

```php
public function __construct(
    private readonly Translator $translator,
) {}

public function example(): void
{
    // Get current locale
    $locale = $this->translator->getLocale();

    // Set locale
    $this->translator->setLocale('cs_CZ');

    // Translate
    $text = $this->translator->translate('user.form.email.label');

    // With parameters
    $text = $this->translator->translate('greeting', ['name' => 'Jan']);
}
```

## Using in Latte Templates

Translations are auto-registered:

```latte
{_'user.form.email.label'}
{_'greeting', name: $userName}
```

## REST API

Fetch all translations for a locale:

```
GET /api/v1/translations/{locale}
```

Response is cached for 1 hour.

## CLI Commands

### translation:import

Imports translations from `.neon` files to database and invalidates cache:

```bash
bin/console translation:import
```

Output:

```
Starting translation import from .neon files...

Import completed successfully!
Translation cache invalidated

Current Language Statistics:

+-------+---------+---------+---------+-------+-----------+---------+---------+
| Code  | Name    | Default | Enabled | Total | From Neon | From DB | Deleted |
+-------+-------+---------+---------+-------+-----------+---------+---------+
| en_US | English | ✓       | ✓       | 3     | 3         | 0       | 0       |
| cs_CZ | Čeština |         | ✓       | 3     | 0         | 3       | 0       |
+-------+---------+---------+---------+-------+-----------+---------+---------+
```

### translation:list

Shows all languages and their translation statistics:

```bash
bin/console translation:list
```

Output:

```
Available Languages:

+-------+---------+---------+---------+-------+-----------+---------+---------+
| Code  | Name    | Default | Enabled | Total | From Neon | From DB | Deleted |
+-------+---------+---------+---------+-------+-----------+---------+---------+
| en_US | English | ✓       | ✓       | 3     | 3         | 0       | 2       |
+-------+---------+---------+---------+-------+-----------+---------+---------+
```

### Statistics Columns

| Column    | Description                                  |
|-----------|----------------------------------------------|
| Code      | Locale code (e.g. `en_US`, `cs_CZ`)          |
| Name      | Display name of the language                 |
| Default   | ✓ if this is the default locale              |
| Enabled   | ✓ if language is active, ✗ if disabled       |
| Total     | Total number of active translations          |
| From Neon | Translations loaded from `.neon` files       |
| From DB   | Translations added/overridden in database    |
| Deleted   | Translations marked as deleted (soft-delete) |
