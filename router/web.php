<?php declare(strict_types=1);

use App\Dashboard\Http\Controller\DashboardController;
use App\Home\Http\Controller\HomeController;
use App\User\Http\Controller\UserController;
use Megio\Translation\Resolver\PosixResolver;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    // Homepage - default locale (without prefix)
    $routes->add('home', '/')
        ->methods(['GET'])
        ->controller([HomeController::class, 'index'])
        ->options(['auth' => false]);

    // Homepage with locale
    $routes->add('home.locale', '/{locale}')
        ->methods(['GET'])
        ->controller([HomeController::class, 'index'])
        ->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])
        ->options(['auth' => false]);

    // User routes with locale prefix
    $routes->add('user.login', '/{locale}/user/login')
        ->methods(['GET'])
        ->controller([UserController::class, 'login'])
        ->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])
        ->options(['auth' => false]);

    $routes->add('user.register', '/{locale}/user/register')
        ->methods(['GET'])
        ->controller([UserController::class, 'register'])
        ->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])
        ->options(['auth' => false]);

    $routes->add('user.activation', '/{locale}/user/activate/{token}')
        ->methods(['GET'])
        ->controller([UserController::class, 'activate'])
        ->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])
        ->options(['auth' => false]);

    $routes->add('user.forgot-password', '/{locale}/user/forgot-password')
        ->methods(['GET'])
        ->controller([UserController::class, 'forgotPassword'])
        ->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])
        ->options(['auth' => false]);

    $routes->add('user.reset-password', '/{locale}/user/reset-password/{token}')
        ->methods(['GET'])
        ->controller([UserController::class, 'resetPassword'])
        ->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])
        ->options(['auth' => false]);

    // Dashboard with locale prefix
    $routes->add('dashboard', '/{locale}/dashboard')
        ->methods(['GET'])
        ->controller([DashboardController::class, 'dashboard'])
        ->requirements(['locale' => PosixResolver::LOCALE_SHORT_PATTERN])
        ->options(['auth' => false]);
};
