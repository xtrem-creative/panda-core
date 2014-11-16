<?php

namespace Panda\Core\Component\Debug;

use Panda\Core\Application;
use Panda\Core\Component\Config\ConfigManager;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Debug
{
    public static function register(Application $app)
    {
        $whoops = new Run;
        if (ConfigManager::exists('app.display_errors') && ConfigManager::get('app.display_errors')) {
            $whoops->pushHandler(new PrettyPageHandler);
        } else {
            $whoops->pushHandler(new ProdHandler($app));
        }
        $whoops->register();
    }
}