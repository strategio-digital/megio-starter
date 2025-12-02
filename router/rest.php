<?php declare(strict_types=1);

use App\Dashboard\Http\Request\DashboardAbstractRequest;
use App\User\Http\Request\ActivateRequest;
use App\User\Http\Request\ForgotPasswordRequest;
use App\User\Http\Request\LoginRequest;
use App\User\Http\Request\RegisterRequest;
use App\User\Http\Request\ResetPasswordRequest;
use Megio\Translation\Resolver\PosixResolver;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (
    RoutingConfigurator $routes,
): void {
    $routes->add('api.user.register', '/api/v1/{locale}/user/register')
        ->methods(['POST'])
        ->controller(RegisterRequest::class)
        ->options(['auth' => false])
        ->requirements(['locale' => PosixResolver::LOCALE_POSIX_PATTERN]);

    $routes->add('api.user.login', '/api/v1/{locale}/user/login')
        ->methods(['POST'])
        ->controller(LoginRequest::class)
        ->options(['auth' => false])
        ->requirements(['locale' => PosixResolver::LOCALE_POSIX_PATTERN]);

    $routes->add('api.user.activate', '/api/v1/{locale}/user/activate')
        ->methods(['POST'])
        ->controller(ActivateRequest::class)
        ->options(['auth' => false])
        ->requirements(['locale' => PosixResolver::LOCALE_POSIX_PATTERN]);

    $routes->add('api.user.forgot-password', '/api/v1/{locale}/user/forgot-password')
        ->methods(['POST'])
        ->controller(ForgotPasswordRequest::class)
        ->options(['auth' => false])
        ->requirements(['locale' => PosixResolver::LOCALE_POSIX_PATTERN]);

    $routes->add('api.user.reset-password', '/api/v1/{locale}/user/reset-password')
        ->methods(['POST'])
        ->controller(ResetPasswordRequest::class)
        ->options(['auth' => false])
        ->requirements(['locale' => PosixResolver::LOCALE_POSIX_PATTERN]);

    $routes->add('api.dashboard.data', '/api/v1/{locale}/dashboard/data')
        ->methods(['GET'])
        ->controller(DashboardAbstractRequest::class)
        ->requirements(['locale' => PosixResolver::LOCALE_POSIX_PATTERN]);
};
