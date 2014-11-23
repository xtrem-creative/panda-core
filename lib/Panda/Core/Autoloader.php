<?php

namespace Panda\Core;

/**
 * Class Autoloader
 * @package Panda\Core
 */
class Autoloader
{
    public static function register($prepend = false)
    {
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
        } else {
            spl_autoload_register(array(__CLASS__, 'autoload'));
        }
    }

    public static function autoload($class)
    {
        $filePath = APP_DIR  . 'bundles' . str_replace('\\', '/', substr($class, strpos($class, '\\')));

        if (is_file($filePath . '.php')) {
            require $filePath . '.php';
        }
    }
}