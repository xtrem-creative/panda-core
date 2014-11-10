<?php

namespace Panda\Core\Component\Router;

use Panda\Core\Component\Router\Exception\NoMatchingRouteException;
use Panda\Core\Component\Router\Exception\NoMatchingRouteMethodException;
use Panda\Core\Component\Router\Provider\Annotation\AnnotationRoutesProvider;
use Panda\Core\Component\Router\Provider\RoutesProvider;

/**
 * A simple routing system manager
 * @package Panda\Core\Component\Router
 */
class Router
{
    private $routesProvider;

    public function __construct()
    {
        $this->setRoutesProvider(new AnnotationRoutesProvider());
    }

    public function findMatchingRoute($url)
    {
        $badMethod = false;
        $routes = $this->routesProvider->getRoutesList();

        foreach ($routes as $route) {
            if ($route->match($url)) {
                if (is_array($route->getHttpMethod()) && !in_array($_SERVER['REQUEST_METHOD'],
                            $route->getHttpMethod())) {
                    $badMethod = true;
                } else {
                    return $route;
                }
            }
        }
        if ($badMethod) {
            throw new NoMatchingRouteMethodException("This url has no route to match with,
            with the given HTTP method.");
        } else {
            throw new NoMatchingRouteException("This url has no route to match with.");
        }
    }

    public function getRoutesProvider()
    {
        return $this->routesProvider;
    }

    public function setRoutesProvider(RoutesProvider $routesProvider)
    {
        $this->routesProvider = $routesProvider;
    }
} 