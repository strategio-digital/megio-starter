<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('home', '/')
        ->methods(['GET'])
        ->controller([\App\Home\Http\Controller\HomeController::class, 'index'])
        ->options(['auth' => false]);
};