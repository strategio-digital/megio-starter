<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('download', '/api/invoice/download')
        ->methods(['POST'])
        ->controller(\App\Admin\Http\Request\ExampleRequest::class)
        ->options(['auth' => false]);
};