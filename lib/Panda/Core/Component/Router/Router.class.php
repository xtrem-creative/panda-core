<?php

namespace Panda\Core\Component\Router;

use Panda\Core\Component\Router\Exception\NoMatchingRouteException;
use Panda\Core\Tool\Annotation\AnnotationParserImpl;

/**
 * A simple routing system manager
 * @package Panda\Core\Component\Router
 */
class Router
{
    private $routes = array();

    public function __construct()
    {
        $this->reloadRoutes();
    }

    public function reloadRoutes($reloadCache = false)
    {
        if (empty($routes) || $reloadCache) {
            $annotationParser = new AnnotationParserImpl();
            $annotationParser->parse('ExampleController');
        }
    }

    public function addRoute($pattern, $bundle, $action, $httpMethod = array('GET', 'POST'))
    {

    }

    public function findMatchingRoute($url)
    {
        foreach ($this->routes as $route) {
            if ($route->match($url)) {
                return $route;
            }
        }
        throw new NoMatchingRouteException("This url has no route to match with.");
    }

    public function getRoutes()
    {
        return $this->routes;
    }
} 