<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('download', '/api/invoice/download')
        ->methods(['POST'])
        ->controller(\App\Http\Request\Invoice\DownloadRequest::class)
        ->options(['auth' => false]);
};