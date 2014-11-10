<?php

namespace Panda\Core\Component\Router\Provider\File;

use Panda\Core\Component\Router\Provider\AbstractRoutesProvider;

class FileRoutesProvider extends AbstractRoutesProvider
{
    private $routeFilesParser;

    public function __construct()
    {
        $this->routeFilesParser = new RouteFilesParser();

        //Load basic attributes knowledge
        $this->routeFilesParser->addKnownAttribute
        ('Panda\Core\Component\Router\Provider\File\Attribute\ActionAttribute', 'action');
    }

    public function reloadRoutes($reloadCache = false)
    {
        if ($reloadCache || empty($this->routes)) {
            $bundlesRoutesFiles = glob(APP_DIR . '*Bundle/res/config/routes.php');

            foreach ($bundlesRoutesFiles as $routesFile) {
                $routesData = $this->routeFilesParser->parse($routesFile);

                foreach ($routesData as $route) {
                    $this->addRoute(
                        $route['urlPattern']->getValue(),
                        $route['bundle']->getName(),
                        $route['action']->getName(),
                        array_key_exists('method', $route) ? $route['method']->getValue() : array('GET', 'POST'),
                        array_key_exists('vars', $route) ? $route['vars']->getValue() : array()
                    );
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