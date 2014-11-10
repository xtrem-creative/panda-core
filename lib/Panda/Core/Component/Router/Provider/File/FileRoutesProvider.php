<?php

namespace Panda\Core\Component\Router\Provider\File;

use Panda\Core\Component\Router\Provider\AbstractRoutesProvider;

class FileRoutesProvider extends AbstractRoutesProvider
{
    private $routes = array();

    public function reloadRoutes($reloadCache = false)
    {
        if ($reloadCache || empty($this->routes)) {
            $bundlesRoutesFiles = glob(APP_DIR . '*Bundle/res/config/routes.php');

            foreach ($bundlesRoutesFiles as $routesFile) {
                $routes = require_once $routesFile;
                $bundleName = str_replace('/res/config/routes.php', '', str_replace(APP_DIR, '', $routesFile));

                foreach ($routes as $route => $config) {
                    if (array_key_exists($route, $this->_routes)) {
                        throw new \RuntimeException('Duplicate route "'.$route.'" in "'.$bundleName.'"');

                    } else {
                        $vars = array_key_exists('vars', $config) ? $config['vars'] : null;
                        $method = array_key_exists('method', $config) ? explode('|',
                            $config['method']) : array('GET', 'POST');
                        $this->addRoute($route, $bundleName, $config['action'], $method, $vars);
                    }
                }
            }

            $this->saveCache();
        } else {
            $this->loadCache();
        }
    }

    public function getRoutesList()
    {
        return $this->routes;
    }
}