<?php
declare(strict_types=1);

use Megio\Bootstrap;
use Megio\Debugger\JsonLogstashLogger;
use Megio\Debugger\SentryLogger;
use Megio\Helper\Path;
use Megio\Http\Kernel\App;

require_once __DIR__ . '/../vendor/autoload.php';

$startedAt = microtime(true);
$container = new Bootstrap()
    ->projectRootPath(__DIR__ . '/../')
    ->logger(
        $_ENV['APP_ENVIRONMENT'] === 'develop'
            ? new JsonLogstashLogger()
            : new SentryLogger(),
    )
    ->configure(Path::appDir() . '/app.neon', $startedAt);

/** @var App $app */
$app = $container->getByType(App::class);
$app->run();
