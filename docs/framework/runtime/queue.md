---
layout: 'page'
uri: '/runtime/queue'
position: 3
slug: 'runtime-queue'
parent: 'runtime'
navTitle: 'Queue Workers'
title: 'Queue Workers'
description: 'Background job processing with database-backed queue, automatic retries, and admin panel monitoring.'
---

# Queue Workers

Background job processing with database-backed queue, automatic retries, and admin panel monitoring.

## How It Works

1. **Facades** dispatch jobs to queue via `QueueRepository::add()`
2. **Queue entity** stores job in database with status, payload, priority
3. **CLI workers** run in background, poll for pending jobs
4. **Worker classes** process jobs and return `null` (done) or `QueueDelay` (reschedule)
5. **Auto-retry** handles failures with exponential backoff (max 5 retries)
6. **Admin panel** provides overview at `/app/settings/queue`

## Configuration

Add environment variable to `.env`:

```dotenv
QUEUE_WORKERS_ENABLED=false
```

Set to `true` in production to enable automatic worker startup via `docker-entrypoint.sh`.

## Directory Structure

```
app/
├── QueueWorker.php                    # Enum mapping worker names to classes
└── DomainName/
    ├── Worker/                        # Worker classes (implement IQueueWorker)
    │   └── UserRegistrationMailWorker.php
    └── Facade/                        # Facades dispatch jobs to queue
        └── UserAuthFacade.php
```

## Creating Queue Workers

### 1. Create worker class

Place in `app/{Domain}/Worker/` implementing `IQueueWorker`:

```php
<?php
declare(strict_types=1);

namespace App\User\Worker;

use App\EntityManager;
use App\User\Mail\UserRegistrationMailer;
use Exception;
use Megio\Database\Entity\Queue;
use Megio\Queue\IQueueWorker;
use Megio\Queue\QueueDelay;
use Megio\Translation\Translator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

use function is_string;

final readonly class UserRegistrationMailWorker implements IQueueWorker
{
    public function __construct(
        private EntityManager $em,
        private UserRegistrationMailer $userRegistrationMailer,
        private Translator $translator,
    ) {}

    /**
     * @throws Exception
     */
    public function process(
        Queue $queueJob,
        OutputInterface $output,
    ): ?QueueDelay {
        $payload = $queueJob->getPayload();
        $user_id = $payload['user_id'] ?? null;
        $posix = $payload['posix'] ?? null;

        // Set locale from payload (queue runs outside HTTP context)
        if (is_string($posix) === false) {
            throw new Exception('Posix locale is missing in the job payload.');
        }
        $this->translator->setPosix($posix);

        // Now translations work correctly in Mailer
        // e.g. $greeting = $this->translator->translate('user.mail.greeting');

        // Validate payload
        if (is_string($user_id) === false) {
            throw new Exception('Invalid user_id type in job payload.');
        }

        if (Uuid::isValid($user_id) === false) {
            throw new Exception('Invalid user_id format in job payload.');
        }

        // Find entity and process
        $user = $this->em->getUserRepo()->findOneBy(['id' => $user_id]);

        if ($user === null) {
            throw new Exception('User not found: ' . $user_id);
        }

        $this->userRegistrationMailer->send($user);

        // Return null = job completed successfully
        return null;
    }
}
```

### 2. Register in QueueWorker enum

Add case to `app/QueueWorker.php`:

```php
<?php
declare(strict_types=1);

namespace App;

use App\User\Worker\UserPasswordResetMailWorker;
use App\User\Worker\UserRegistrationMailWorker;
use Megio\Queue\IQueueWorkerEnum;

enum QueueWorker: string implements IQueueWorkerEnum
{
    case USER_REGISTRATION_MAIL_WORKER = 'user.registration.mail.worker';
    case USER_PASSWORD_RESET_MAIL_WORKER = 'user.password.reset.mail.worker';

    public function className(): string
    {
        return match ($this) {
            self::USER_REGISTRATION_MAIL_WORKER => UserRegistrationMailWorker::class,
            self::USER_PASSWORD_RESET_MAIL_WORKER => UserPasswordResetMailWorker::class,
        };
    }
}
```

### 3. Add to docker-entrypoint.sh

Register worker startup in `docker-entrypoint.sh`:

```bash
if [ "$QUEUE_WORKERS_ENABLED" = "true" ]; then
  echo "Starting queue workers..."
  start_queue_worker "php bin/console app:queue user.registration.mail.worker"
  start_queue_worker "php bin/console app:queue user.password.reset.mail.worker"
fi
```

## Dispatching Jobs

Dispatch from Facade using `QueueRepository::add()`:

```php
$this->em->getQueueRepo()->add(
    worker: QueueWorker::USER_REGISTRATION_MAIL_WORKER,
    payload: [
        'user_id' => $user->getId(),
        'posix' => $this->translator->getPosix(),
    ],
);
```

### With priority

Higher priority jobs are processed first:

```php
$this->em->getQueueRepo()->add(
    worker: QueueWorker::IMPORTANT_WORKER,
    payload: ['data' => $data],
    priority: 10,  // Higher = processed sooner
);
```

### With delay

Schedule job for later execution:

```php
use Megio\Queue\QueueDelay;

$this->em->getQueueRepo()->add(
    worker: QueueWorker::SCHEDULED_WORKER,
    payload: ['data' => $data],
    delay: new QueueDelay(
        delayUntil: new DateTime('+1 hour'),
        delayReason: 'Rate limiting',
    ),
);
```

## Rescheduling Jobs

Return `QueueDelay` from worker to reschedule:

```php
public function process(Queue $queueJob, OutputInterface $output): ?QueueDelay
{
    // Check if external service is available
    if ($this->externalService->isAvailable() === false) {
        return new QueueDelay(
            delayUntil: new DateTime('+30 minutes'),
            delayReason: 'External service unavailable',
        );
    }

    // Process job...
    return null;
}
```

## Job Statuses

| Status | Description |
|--------|-------------|
| `pending` | Waiting to be processed |
| `processing` | Currently being processed by a worker |
| `failed` | Failed after max retries (5 attempts) |

## Auto-Retry Behavior

When a worker throws an exception:

1. Job status remains `pending`
2. `errorRetries` counter increments
3. Job is delayed by 30 minutes
4. After 5 retries, status changes to `failed`
5. Error message is stored in `errorMessage` field

## CLI Commands

### Run worker

```bash
bin/console app:queue {worker-name}
```

Example:

```bash
bin/console app:queue user.registration.mail.worker
```

The worker:
- Processes up to 100 jobs per startup
- Sleeps 1 second between jobs (prevents CPU spikes)
- Automatically restarts after 100 jobs
- Logs to `log/queue-worker.log`

### Run in development

```bash
docker compose exec app bin/console app:queue user.registration.mail.worker
```

## Admin Panel

View and manage queue jobs at `/app/settings/queue`:

- List all jobs with status, worker, payload
- View job details including error messages
- Manually edit/retry failed jobs
- Create new jobs for testing

## Queue Rules

### Where to Dispatch Jobs From (allowed)

- **Facades** - dispatch jobs after completing business operations
- **Queue workers** - can dispatch follow-up jobs

### Where NOT to Dispatch Jobs From (forbidden)

- **Controllers** - no direct queue access
- **Request handlers** - no direct queue access
- **Repositories** - no queue operations
- **Entities** - no queue operations
- **Mailers** - should be called by workers, not dispatch jobs

### Required Patterns

- Always use `final readonly` class for Workers
- Always include `posix` in payload (queue runs outside HTTP context)
- Always validate payload data before processing
- Use English-only exception messages (for developers)
- Return `null` when job completes successfully
- Return `QueueDelay` to reschedule for later
- Register all workers in `QueueWorker` enum
- Add worker startup to `docker-entrypoint.sh`
