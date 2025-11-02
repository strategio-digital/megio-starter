<?php declare(strict_types=1);

use App\Dashboard\Http\Request\DashboardRequest;
use App\User\Http\Request\UserActivateRequest;
use App\User\Http\Request\UserForgotPasswordRequest;
use App\User\Http\Request\UserLoginRequest;
use App\User\Http\Request\UserRegisterRequest;
use App\User\Http\Request\UserResetPasswordRequest;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (
    RoutingConfigurator $routes,
): void {
    $routes->add('api.user.register', '/api/v1/user/register')
        ->methods(['POST'])
        ->controller(UserRegisterRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.user.login', '/api/v1/user/login')
        ->methods(['POST'])
        ->controller(UserLoginRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.user.activate', '/api/v1/user/activate')
        ->methods(['POST'])
        ->controller(UserActivateRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.user.forgot-password', '/api/v1/user/forgot-password')
        ->methods(['POST'])
        ->controller(UserForgotPasswordRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.user.reset-password', '/api/v1/user/reset-password')
        ->methods(['POST'])
        ->controller(UserResetPasswordRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.dashboard.data', '/api/v1/dashboard/data')
        ->methods(['GET'])
        ->controller(DashboardRequest::class);
};
