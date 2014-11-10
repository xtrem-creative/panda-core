<?php

namespace Panda\Core\Component\Router\Provider;


use Panda\Core\Component\Router\Route;

abstract class AbstractRoutesProvider implements RoutesProvider
{
    protected $routes = array();

    public function getRoutesList()
    {
        if (empty($this->routes)) {
            $this->reloadRoutes();
        }
        return $this->routes;
    }

    protected function loadCache()
    {
        if (!is_dir(RESOURCES_DIR . 'cache/router/')) {
            mkdir(RESOURCES_DIR . 'cache/router/');
        }
        if (!is_file(RESOURCES_DIR . 'cache/router/routes.cache')) {
            $this->saveCache();
        }
        $this->routes = unserialize(file_get_contents(RESOURCES_DIR . 'cache/router/routes.cache'));
    }

    public function saveCache()
    {
        if (!is_dir(RESOURCES_DIR . 'cache/router/')) {
            mkdir(RESOURCES_DIR . 'cache/router/');
        }
        file_put_contents(RESOURCES_DIR . 'cache/router/routes.cache', serialize($this->routes));
    }

    protected function addRoute($pattern, $bundle, $action, $httpMethod, $vars)
    {
        $this->routes[] = new Route($pattern, $bundle, $action, $httpMethod, $vars);
    }
} 