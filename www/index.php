<?php
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

$startedAt = microtime(true);
$container = (new \Megio\Bootstrap())
    ->projectRootPath(__DIR__ . '/../')
    ->logger($_ENV['APP_ENVIRONMENT'] === 'develop'
        ? new \Megio\Debugger\JsonLogstashLogger()
        : new \Megio\Debugger\SentryLogger())
    ->configure(\Megio\Helper\Path::configDir() . '/app.neon', $startedAt);

/** @var \Megio\Http\Kernel\App $app */
$app = $container->getByType(\Megio\Http\Kernel\App::class);
$app->run();