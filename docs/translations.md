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
2. **Other languages** are stored in database (can be managed via API)
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

```yaml
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

ICU MessageFormat supports variables, plurals, and select expressions.

```yaml
# Variables
greeting: 'Hello {name}!'

# Plurals (English)
items: '{count, plural, =0 {No items} =1 {One item} other {# items}}'

# Plurals (Czech with grammatical cases)
items_cs: '{count, plural, =0 {Žádné položky} one {# položka} few {# položky} other {# položek}}'

# Select
status: '{status, select, active {Active} inactive {Inactive} other {Unknown}}'
```

Usage:

```latte
{_'app.items', ['count' => 5]}
```

```typescript
t('app.items', { count: 5 })
```

```php
$this->translator->translate('app.items', ['count' => 5]);
```

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

+-------+-------+---------+---------+---------+-------+-----------+---------+---------+
| Posix | Code  | Name    | Default | Enabled | Total | From Neon | From DB | Deleted |
+-------+-------+---------+---------+---------+-------+-----------+---------+---------+
| en_US | en    | English | ✓       | ✓       | 3     | 3         | 0       | 0       |
| cs_CZ | cs    | Čeština |         | ✓       | 3     | 0         | 3       | 0       |
+-------+-------+---------+---------+---------+-------+-----------+---------+---------+
```

### translation:list

Shows all languages and their translation statistics:

```bash
bin/console translation:list
```

Output:

```
Available Languages:

+-------+-------+---------+---------+---------+-------+-----------+---------+---------+
| Posix | Code  | Name    | Default | Enabled | Total | From Neon | From DB | Deleted |
+-------+-------+---------+---------+---------+-------+-----------+---------+---------+
| en_US | en    | English | ✓       | ✓       | 3     | 3         | 0       | 2       |
+-------+-------+---------+---------+---------+-------+-----------+---------+---------+
```

### Statistics Columns

| Column    | Description                                  |
|-----------|----------------------------------------------|
| Posix     | POSIX locale (e.g. `en_US`, `cs_CZ`)         |
| Code      | Short code for URLs (e.g. `en`, `cs`)        |
| Name      | Display name of the language                 |
| Default   | ✓ if this is the default locale              |
| Enabled   | ✓ if language is active, ✗ if disabled       |
| Total     | Total number of active translations          |
| From Neon | Translations loaded from `.neon` files       |
| From DB   | Translations added/overridden in database    |
| Deleted   | Translations marked as deleted (soft-delete) |

## Using in PHP

Inject `Megio\Translation\Translator`:

```php
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
        // Get current POSIX locale
        bdump($this->translator->getPosix()); // 'cs_CZ'

        return $this->render(Path::viewDir() . '/dashboard/controller/dashboard.latte', [
            'title' => $this->translator->translate('dashboard.page.title'),
            'description' => $this->translator->translate('dashboard.page.description'),
        ]);
    }
}
```

### Using in Queue Workers

Queue workers run outside HTTP context, so locale must be passed in payload and set manually:

```php
// Dispatching job with posix parameter
$this->em->getQueueRepo()->add(
    worker: QueueWorker::USER_REGISTRATION_MAIL_WORKER,
    payload: [
        'user_id' => $user->getId(),
        'posix' => $this->translator->getPosix(),
    ],
);
```

```php
// Processing job - set locale from payload
final readonly class UserRegistrationMailWorker implements IQueueWorker
{
    public function __construct(
        private Translator $translator,
    ) {}

    public function process(Queue $queueJob, OutputInterface $output): ?QueueDelay
    {
        $payload = $queueJob->getPayload();
        $posix = $payload['posix'] ?? null;

        if (is_string($posix) === false) {
            throw new Exception('Posix locale is missing in the job payload.');
        }

        $this->translator->setPosix($posix);

        // Now translations work correctly
        $subject = $this->translator->translate('user.mail.registration.subject');
        // ...
    }
}
```

## Using in Latte Templates

Translations are auto-registered:

```latte
{_'user.form.email.label'}
{_'app.greeting', ['name' => $userName]}
```

## Using in Vue

Use the `useTranslation` composable:

```typescript
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';

const { t, load, setPosix, posix, shortCode } = useTranslation();

// Load translations (call once on app init)
await load();

// Translate
const label = t('user.form.email.label');
const greeting = t('greeting', { name: 'Jan' });

// Reactive locale values
console.log(posix.value);     // 'cs_CZ'
console.log(shortCode.value); // 'cs'

// Change locale dynamically
await setPosix('en_US');
```

### API

| Property | Type | Description |
|----------|------|-------------|
| `t(key, params?)` | `function` | Translate key with optional ICU parameters |
| `load()` | `async function` | Load translations from API |
| `setPosix(posix)` | `async function` | Change locale and reload translations |
| `posix` | `ComputedRef<string>` | Reactive POSIX locale (e.g., `cs_CZ`) |
| `shortCode` | `ComputedRef<string>` | Reactive short locale (e.g., `cs`) |

## REST API

Fetch all translations for a locale (POSIX format required):

```
GET /megio/translation/fetch/{locale}
```

Example: `GET /megio/translation/fetch/cs_CZ`

Response is cached for 2 hours (when caching is enabled).

## Locale Formats

| Format | Example | Usage |
|--------|---------|-------|
| shortCode | `cs` | URL paths (`/cs/dashboard`) |
| POSIX | `cs_CZ` | API requests, database, backend |
| BCP 47 | `cs-CZ` | HTML `lang` attribute |

## Request Flow

1. **User visits** `/cs/dashboard` (shortCode in URL)
2. **TranslationDetectSubscriber** intercepts the request
3. **PosixResolver** resolves `cs` + `Accept-Language` header → `cs_CZ`
4. **Translator** sets locale to `cs_CZ`
5. **Layout** renders with `lang="cs-CZ"` (BCP 47) and `data-posix="cs_CZ"`
6. **Vue composable** reads `data-posix` and fetches translations
7. **API requests** from frontend use POSIX format (`/megio/translation/fetch/cs_CZ`)

## Best Practices

### Where to Translate (output layer)

- **Vue components** - `t('key')` in templates
- **Latte templates** - `{_'key'}`
- **Mailers** - email subjects and bodies
- **Controllers** - only exceptionally (page titles)
- **Request handlers** - only exceptionally (API error responses)
- **Queue workers** - see [Using in Queue Workers](#using-in-queue-workers)

### Where NOT to Translate

- **Facades** - throw keys only, never translate
- **Repositories** - no translations
- **Entities** - no translations
- **Other services** - no translations
- **CLI commands** - developers only, English is fine
- **Internal exceptions** - `RuntimeException`, `InvalidArgumentException`
- **Log messages** - always English for consistency

### TranslatableException

When you need to translate exceptions (only exceptionally), use `TranslatableExceptionInterface`:

```php
// Facade - only key
throw new TranslatableException('user.error.email_exists');

// With parameters
throw new TranslatableException(
    translationKey: 'user.error.min_age',
    translationParams: ['minAge' => 18],
);

// Controller/Request handler - translates for response
catch (TranslatableExceptionInterface $e) {
    return $this->error(
        $this->translator->translate($e->getTranslationKey(), $e->getTranslationParams())
    );
}
```

## Generate translations with AI

Prompt for CLI agent (Claude Code, Cursor, etc.):

```
WORKFLOW: Adding translations from .neon file to database for non-default locale

STEP 0: Ask user for required inputs (DO NOT proceed without these):
- Ask: "What is the target POSIX locale? (e.g., cs_CZ, de_DE, fr_FR)"
- Ask: "What is the source .neon file path? (e.g., locale/validator.locale.en_US.neon)"
Wait for user response before continuing.

STEP 1: Get or create language for target locale
- Run: docker compose exec postgres psql -U root -d dev-appname -c "SELECT id, posix FROM language;"
- If target locale exists, note the UUID
- If target locale does NOT exist, create it:
  docker compose exec postgres psql -U root -d dev-appname -c \
    "INSERT INTO language (id, posix, short_code, name, is_default, is_enabled, created_at, updated_at)
     VALUES (uuidv7(), '<POSIX>', '<SHORT_CODE>', '<LANGUAGE_NAME>', false, true, NOW(), NOW())
     RETURNING id;"
  Examples:
  - cs_CZ: posix='cs_CZ', short_code='cs', name='Čeština'
  - de_DE: posix='de_DE', short_code='de', name='Deutsch'
  - fr_FR: posix='fr_FR', short_code='fr', name='Français'

STEP 2: Count existing translations (for later verification)
- Run: docker compose exec postgres psql -U root -d dev-appname -c "SELECT COUNT(*) FROM language_translation WHERE language_id = '<LANGUAGE_ID>' AND domain = '<DOMAIN>';"
- Domain is extracted from filename: locale/{domain}.locale.{POSIX}.neon

STEP 3: For each key in NEON file, translate and insert
- Translate English ICU message to target language
- Czech plurals use 3 forms: one (1), few (2-4), other (5+)
- Example EN: {limit, plural, one {# character} other {# characters}}
- Example CS: {limit, plural, one {# znak} few {# znaky} other {# znaků}}
- Run for each key:
  docker compose exec postgres psql -U root -d dev-appname -c \
    "INSERT INTO language_translation (id, key, domain, value, is_from_source, is_deleted, created_at, updated_at, language_id)
     VALUES (uuidv7(), '<KEY>', '<DOMAIN>', '<TRANSLATED_ICU_MESSAGE>', false, false, NOW(), NOW(), '<LANGUAGE_ID>')
     ON CONFLICT (key, domain, language_id) DO NOTHING;"

STEP 4: Verify count matches number of keys in NEON file
- Run: docker compose exec postgres psql -U root -d dev-appname -c "SELECT COUNT(*) FROM language_translation WHERE language_id = '<LANGUAGE_ID>' AND domain = '<DOMAIN>';"

STEP 5: Invalidate cache
- Run: docker compose exec app rm -rf /app/temp/translations

STEP 6: Verify translations were imported
- Run: docker compose exec app bin/console translation:list
```

