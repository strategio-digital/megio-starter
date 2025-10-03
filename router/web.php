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

    $routes->add('login', '/login')
        ->methods(['GET'])
        ->controller([UserController::class, 'login'])
        ->options(['auth' => false]);

    $routes->add('register', '/register')
        ->methods(['GET'])
        ->controller([UserController::class, 'register'])
        ->options(['auth' => false]);

    $routes->add('dashboard', '/dashboard')
        ->methods(['GET'])
        ->controller([DashboardController::class, 'dashboard'])
        ->options(['auth' => false]);
};
