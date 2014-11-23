<?php

namespace Panda\Core\Component\Router\Provider\File;

use Panda\Core\Component\Router\Provider\AbstractRoutesProvider;

class FileRoutesProvider extends AbstractRoutesProvider
{
    private $routeFilesParser;

    public function __construct(array $bundles)
    {
        $this->routeFilesParser = new RouteFilesParser();

        //Load basic attributes knowledge
        $this->routeFilesParser->addKnownAttribute
        ('Panda\Core\Component\Router\Provider\File\Attribute\ActionAttribute', 'action');

        $this->bundles = $bundles;
    }

    public function reloadRoutes($reloadCache = false)
    {
        if ($reloadCache || empty($this->routes)) {

            foreach ($this->bundles as $bundle => $controller) {
                $routesFile = dirname($controller->getFileName()) . '/res/config/routes.php';
                $routesData = $this->routeFilesParser->parse($routesFile);

                foreach ($routesData as $route) {
                    $this->addRoute(
                        $route['urlPattern']->getValue(),
                        $controller->getNamespaceName(),
                        $route['bundle']->getName(),
                        $route['action']->getName() . 'Action',
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
}