<?php declare(strict_types=1);

use App\Dashboard\Http\Request\DashboardRequest;
use App\User\Http\Request\UserActivateRequest;
use App\User\Http\Request\UserRegisterRequest;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (
    RoutingConfigurator $routes,
): void {
    $routes->add('api.user.register', '/api/v1/user/register')
        ->methods(['POST'])
        ->controller(UserRegisterRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.user.activate', '/api/v1/user/activate')
        ->methods(['POST'])
        ->controller(UserActivateRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.dashboard.data', '/api/v1/dashboard/data')
        ->methods(['GET'])
        ->controller(DashboardRequest::class);
};
