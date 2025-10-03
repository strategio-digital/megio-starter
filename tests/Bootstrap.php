<?php
declare(strict_types=1);

namespace Tests;

use Megio\Bootstrap as MegioBootstrap;
use Tracy\ILogger;

class Bootstrap extends MegioBootstrap
{
    // Disable Tracy logger - exception & error handlers
    public function logger(ILogger $logger): Bootstrap
    {
        return $this;
    }
}
