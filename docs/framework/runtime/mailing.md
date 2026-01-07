---
layout: 'page'
uri: '/runtime/mailing'
position: 4
slug: 'runtime-mailing'
parent: 'runtime'
navTitle: 'Sending Emails'
title: 'Sending Emails'
description: 'Email sending with Latte templates, Tailwind CSS, and background queue processing.'
---

# Sending Emails

Email sending with Latte templates, Tailwind CSS, and background queue processing.

## How It Works

1. **Templates** use Latte syntax with Tailwind CSS via Maizzle
2. **Build process** compiles templates with inlined CSS
3. **Mailer classes** compose and send emails
4. **Queue workers** handle background sending (optional)

## Configuration

Add environment variables to `.env`:

```dotenv
# SMTP Configuration
SMTP_SENDER=app@example.com
SMTP_HOST=email-smtp.eu-central-1.amazonaws.com
SMTP_USERNAME=your_username
SMTP_PASSWORD=your_password
SMTP_PORT=587
SMTP_ENCRYPTION=tls

# Email Branding
MAIL_SENDER_NAME='App Name'
MAIL_SIGNATURE_TITLE='App Name'
MAIL_SIGNATURE_TEXT='Your tagline here'
```

## Directory Structure

```
app/DomainName/
├── Mail/                           # Mailer classes
│   └── UserRegistrationMailer.php
└── Worker/                         # Queue workers for async sending
    └── UserRegistrationMailWorker.php

view/
├── mail/                           # Shared mail components
│   ├── layout/
│   │   └── main.html               # Base layout with header/footer
│   └── component/
│       ├── button.html             # CTA button
│       ├── divider.html            # Horizontal line
│       └── spacer.html             # Vertical spacing
└── domain/
    └── mail/
        └── template-name.mail.latte  # Email template
```

## Creating Email Templates

### 1. Create template file

Place in `view/{domain}/mail/` with `.mail.latte` extension:

```latte
{* view/user/mail/user-registration.mail.latte *}
{varType Megio\Mailer\EmailTemplate $template}

{var $activationLink = $template->getParam('activationLink')}
{varType string $activationLink}

<x-main>
    <h1 class="m-0 mb-6 text-2xl sm:leading-8 text-black font-semibold">
        {$template->getSubject()}
    </h1>

    <x-divider height="1px" className="bg-slate-200" />

    <p class="m-0 leading-6 mt-6">
        <strong>{_'user.mail.greeting'}</strong>
    </p>

    <p class="m-0 leading-6 mt-4">
        {_'user.mail.registration.body'}
    </p>

    <x-spacer height="24px" />

    <x-button href="{$activationLink}">
        {_'user.mail.registration.button'}
    </x-button>

    <x-spacer height="24px" />
</x-main>
```

### 2. Available components

| Component | Props | Description |
|-----------|-------|-------------|
| `<x-main>` | - | Base layout wrapper with header/footer |
| `<x-button>` | `href` | CTA button with blue background |
| `<x-divider>` | `height`, `className` | Horizontal separator line |
| `<x-spacer>` | `height` | Vertical spacing |

### 3. Build templates

```bash
yarn mail
```

This compiles `.mail.latte` files to `temp/latte-mail/` with inlined Tailwind CSS.

## Creating Mailer Classes

Create in `app/{Domain}/Mail/` as `final readonly` class:

```php
<?php
declare(strict_types=1);

namespace App\User\Mail;

use App\User\Database\Entity\User;
use Megio\Helper\EnvConvertor;
use Megio\Helper\Path;
use Megio\Http\Resolver\LinkResolver;
use Megio\Mailer\EmailTemplate;
use Megio\Mailer\EmailTemplateFactory;
use Megio\Mailer\SmtpMailer;
use Megio\Translation\Translator;
use Nette\Mail\Message;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class UserRegistrationMailer
{
    public function __construct(
        private LinkResolver $linkResolver,
        private EmailTemplateFactory $emailTemplateFactory,
        private Translator $translator,
    ) {}

    public function send(User $user): void
    {
        // Build dynamic link
        $activationLink = $this->linkResolver->link('user.activation', [
            'locale' => $this->translator->getShortCode(),
            'token' => $user->getActivationToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        // Create template with params
        $template = new EmailTemplate(
            file: Path::viewDir() . '/user/mail/user-registration.mail.latte',
            subject: $this->translator->translate('user.mail.registration.subject'),
            params: [
                'activationLink' => $activationLink,
            ],
        );

        // Build message
        $message = new Message()
            ->setSubject($template->getSubject())
            ->setHtmlBody($this->emailTemplateFactory->render($template));

        $message
            ->addTo($user->getEmail())
            ->setFrom(
                email: EnvConvertor::toString($_ENV['SMTP_SENDER']),
                name: EnvConvertor::toString($_ENV['MAIL_SENDER_NAME']),
            )
            ->addBcc(EnvConvertor::toString($_ENV['APP_DEVELOPER_MAIL']));

        // Send
        $mailer = new SmtpMailer();
        $mailer->send($message);
    }
}
```

## Background Queue Processing

For better UX, send emails via queue workers.

### 1. Create queue worker

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

final readonly class UserRegistrationMailWorker implements IQueueWorker
{
    public function __construct(
        private EntityManager $em,
        private UserRegistrationMailer $userRegistrationMailer,
        private Translator $translator,
    ) {}

    public function process(Queue $queueJob, OutputInterface $output): ?QueueDelay
    {
        $payload = $queueJob->getPayload();
        $user_id = $payload['user_id'] ?? null;
        $posix = $payload['posix'] ?? null;

        // Set locale from payload (queue runs outside HTTP context)
        if (is_string($posix) === false) {
            throw new Exception('Posix locale is missing in the job payload.');
        }
        $this->translator->setPosix($posix);

        // Now translations work correctly in Mailer
        // e.g. $this->translator->translate('user.mail.greeting')

        // Validate user_id
        if (is_string($user_id) === false || Uuid::isValid($user_id) === false) {
            throw new Exception('Invalid user_id in job payload.');
        }

        // Find user and send
        $user = $this->em->getUserRepo()->findOneBy(['id' => $user_id]);
        if ($user === null) {
            throw new Exception('User not found: ' . $user_id);
        }

        $this->userRegistrationMailer->send($user);

        return null;
    }
}
```

### 2. Dispatch job from facade

```php
$this->em->getQueueRepo()->add(
    worker: QueueWorker::USER_REGISTRATION_MAIL_WORKER,
    payload: [
        'user_id' => $user->getId(),
        'posix' => $this->translator->getPosix(),
    ],
);
```

**Important:** Always include `posix` in payload - queue workers run outside HTTP context.

## Register in DI

Add to `app/{Domain}/{domain}.neon`:

```neon
services:
    - App\User\Mail\UserRegistrationMailer
```

Workers are auto-discovered from `app/*/Worker/` directory.

## CLI Commands

### Build mail templates

```bash
yarn mail
```

Compiles all `.mail.latte` files with inlined Tailwind CSS.

### Run tests (includes mail build)

```bash
make test
```

The test suite automatically runs `yarn mail` to ensure templates compile.

## Mailing Rules

### Where to Send Emails From (allowed)

- **Mailer classes** - `app/{Domain}/Mail/` - compose and send emails
- **Queue workers** - call Mailer classes for background sending

### Where NOT to Send Emails From (forbidden)

- **Controllers** - no email sending
- **Request handlers** - no email sending
- **Facades** - dispatch to queue, never send directly
- **Repositories** - no email sending
- **Entities** - no email sending

### Required Patterns

- Always use `final readonly` class for Mailers
- Always include `posix` in queue payloads (queue runs outside HTTP context)
- Always run `yarn mail` before testing (compiles templates with inlined CSS)
- Use `{_'key'}` for translations in Latte templates
- Translate subject in Mailer: `$this->translator->translate('...')`
