<?php declare(strict_types=1);

use App\Dashboard\Http\Request\DashboardRequest;
use App\User\Http\Request\UserRegisterRequest;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('api.user_register', '/api/user/register')
        ->methods(['POST'])
        ->controller(UserRegisterRequest::class)
        ->options(['auth' => false]);

    $routes->add('api.dashboard_data', '/api/dashboard/data')
        ->methods(['GET'])
        ->controller(DashboardRequest::class);
};
