<?php

use Megio\Helper\Path;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import(Path::routerDir() . '/rest.php')->stateless();
    $routes->import(Path::routerDir() . '/web.php')->stateless();
    $routes->import(Path::megioVendorDir() . '/router/app.php')->stateless();
};