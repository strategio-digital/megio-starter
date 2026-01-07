---
layout: 'page'
uri: '/runtime/error-handling'
position: 6
slug: 'runtime-error-handling'
parent: 'runtime'
navTitle: 'Error Handling'
title: 'Error Handling & Logging'
description: 'Sentry integration, Tracy BlueScreen, and JSON Logstash logging for error tracking.'
---

# Error Handling & Logging

Sentry integration, Tracy BlueScreen, and JSON Logstash logging for error tracking.

## How It Works

1. **Exception** is thrown in application
2. **Logger** captures the error (Sentry or Logstash)
3. **Tracy BlueScreen** is generated and optionally uploaded to S3
4. **Email notification** is sent (if configured)
5. **Sentry** receives error with context and stack trace

## Configuration

### Environment Variables

```dotenv
# Logger type: sentry or logstash
APP_LOGGER=sentry

# Sentry DSN (required for sentry logger)
LOG_SENTRY_DSN=https://xxx@sentry.io/xxx

# Optional: Upload BlueScreen to S3
LOG_S3_BLUESCREEN=true

# Optional: Email notifications
LOG_MAIL=admin@example.com
```

### S3 Configuration (for BlueScreen upload)

```dotenv
S3_ENDPOINT=https://s3.eu-central-1.amazonaws.com
S3_REGION=eu-central-1
S3_BUCKET=my-bucket
S3_KEY=AKIAIOSFODNN7EXAMPLE
S3_SECRET=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
```

## Available Loggers

### SentryLogger

Full-featured error tracking with Sentry:

- Captures exceptions with stack trace
- Generates Tracy BlueScreen locally
- Uploads BlueScreen to S3 (optional)
- Sends email notifications (optional)
- Adds breadcrumbs and context

### JsonLogstashLogger

JSON file logging for ELK stack:

- Writes JSON logs to `temp/log/{date}--logstash.json.log`
- Compatible with Logstash/Elasticsearch
- Generates Tracy BlueScreen locally
- Uploads BlueScreen to S3 (optional)

## Log Levels

| Level | Constant | Description |
|-------|----------|-------------|
| DEBUG | `ILogger::DEBUG` | Debug information |
| INFO | `ILogger::INFO` | Informational messages |
| WARNING | `ILogger::WARNING` | Warning conditions |
| ERROR | `ILogger::ERROR` | Error conditions |
| EXCEPTION | `ILogger::EXCEPTION` | Exception occurred |
| CRITICAL | `ILogger::CRITICAL` | Critical conditions |

## Using Logger

### In Facade

```php
<?php
declare(strict_types=1);

namespace App\Order\Facade;

use Tracy\ILogger;

final readonly class ProcessOrderFacade
{
    public function __construct(
        private ILogger $logger,
    ) {}

    public function execute(ProcessOrderDto $dto): Order
    {
        try {
            // Business logic...
            $this->logger->log('Order processed', ILogger::INFO);
            return $order;
        } catch (PaymentException $e) {
            $this->logger->log($e, ILogger::ERROR);
            throw $e;
        }
    }
}
```

### Logging with Context

```php
// Log exception (creates BlueScreen)
$this->logger->log($exception, ILogger::ERROR);

// Log message with context
$this->logger->log([
    'message' => 'Payment failed',
    'orderId' => $order->getId(),
    'amount' => $amount,
    'gateway' => 'stripe',
], ILogger::ERROR);

// Log simple message
$this->logger->log('User logged in', ILogger::INFO);
```

## Tracy BlueScreen

### Local Storage

BlueScreen files are stored in `temp/log/`:

```
temp/log/
└── blue-screen-{hash}.html
```

### S3 Upload

When `LOG_S3_BLUESCREEN=true`, BlueScreen is uploaded to:

```
s3://{bucket}/.tracy/blue-screen-{hash}.html
```

### Tracy Link Format

BlueScreen is accessible via:

```
{APP_URL}/app/logs/tracy/{hash}
```

## Email Notifications

When `LOG_MAIL` is set, emails are sent for:
- ERROR level logs
- EXCEPTION level logs
- CRITICAL level logs

Email is rate-limited (1 per day) to prevent spam.

## Sentry Features

### Breadcrumbs

Additional context added to errors:

```php
$this->logger->log([
    'message' => 'Order processing started',
    'orderId' => $orderId,
    'userId' => $userId,
], ILogger::DEBUG);

// Later, if error occurs, breadcrumbs show the flow
```

### Tracy Context

Sentry automatically includes:
- Tracy BlueScreen hash
- Tracy BlueScreen filename
- Link to BlueScreen file

## Directory Structure

```
temp/
└── log/
    ├── blue-screen-abc123.html      # Tracy BlueScreen
    ├── 2024-01-15--logstash.json.log  # JSON logs
    └── email-sent                    # Email rate limit marker
```

## Logging Rules

### Required Patterns

- Use `ILogger` interface (injected via DI)
- Log exceptions at ERROR level
- Add context for debugging
- Use appropriate log levels

### Forbidden

- Direct `error_log()` calls
- `var_dump()` or `print_r()` in production
- Logging sensitive data (passwords, tokens)
- Logging without context

### Where to Log

| Situation | Level | Context |
|-----------|-------|---------|
| Exception caught | ERROR | Exception object |
| Validation failed | WARNING | Field, value |
| External API error | ERROR | Endpoint, response |
| User action | INFO | User ID, action |
| Debug info | DEBUG | Relevant data |
