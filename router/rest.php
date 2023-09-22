<?php

use App\Http\Request\Invoice\DownloadRequest;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('download', '/api/invoice/download')
        ->methods(['POST'])
        ->controller(DownloadRequest::class)
        ->options(['auth' => false]);
};