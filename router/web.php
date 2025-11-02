<?php declare(strict_types=1);

use App\Dashboard\Http\Controller\DashboardController;
use App\Home\Http\Controller\HomeController;
use App\User\Http\Controller\UserController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('home', '/')
        ->methods(['GET'])
        ->controller([HomeController::class, 'index'])
        ->options(['auth' => false]);

    $routes->add('user.login', '/user/login')
        ->methods(['GET'])
        ->controller([UserController::class, 'login'])
        ->options(['auth' => false]);

    $routes->add('user.register', '/user/register')
        ->methods(['GET'])
        ->controller([UserController::class, 'register'])
        ->options(['auth' => false]);

    $routes->add('user.activation', '/user/activate/{token}')
        ->methods(['GET'])
        ->controller([UserController::class, 'activate'])
        ->options(['auth' => false]);

    $routes->add('user.forgot-password', '/user/forgot-password')
        ->methods(['GET'])
        ->controller([UserController::class, 'forgotPassword'])
        ->options(['auth' => false]);

    $routes->add('user.reset-password', '/user/reset-password/{token}')
        ->methods(['GET'])
        ->controller([UserController::class, 'resetPassword'])
        ->options(['auth' => false]);

    $routes->add('dashboard', '/dashboard')
        ->methods(['GET'])
        ->controller([DashboardController::class, 'dashboard'])
        ->options(['auth' => false]);
};
