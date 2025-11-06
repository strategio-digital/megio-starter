<?php declare(strict_types=1);

use App\Dashboard\Http\Request\DashboardAbstractRequest;
use App\User\Http\Request\UserActivateAbstractRequest;
use App\User\Http\Request\UserForgotPasswordAbstractRequest;
use App\User\Http\Request\UserLoginAbstractRequest;
use App\User\Http\Request\UserRegisterAbstractRequest;
use App\User\Http\Request\UserResetPasswordAbstractRequest;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (
    RoutingConfigurator $routes,
): void {
    $routes->add('api.user.register', '/api/v1/user/register')
        ->methods(['POST'])
        ->controller(UserRegisterAbstractRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.user.login', '/api/v1/user/login')
        ->methods(['POST'])
        ->controller(UserLoginAbstractRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.user.activate', '/api/v1/user/activate')
        ->methods(['POST'])
        ->controller(UserActivateAbstractRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.user.forgot-password', '/api/v1/user/forgot-password')
        ->methods(['POST'])
        ->controller(UserForgotPasswordAbstractRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.user.reset-password', '/api/v1/user/reset-password')
        ->methods(['POST'])
        ->controller(UserResetPasswordAbstractRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.dashboard.data', '/api/v1/dashboard/data')
        ->methods(['GET'])
        ->controller(DashboardAbstractRequest::class);
};
