<?php

namespace Panda\Core\Component\Router;

use Panda\Core\Component\Router\Exception\NoMatchingRouteException;
use Panda\Core\Component\Router\Exception\NoMatchingRouteMethodException;
use Panda\Core\Tool\Annotation\AnnotationParserImpl;

/**
 * A simple routing system manager
 * @package Panda\Core\Component\Router
 */
class Router
{
    private $routes = array();
    private $annotationParser;

    public function __construct()
    {
        $this->annotationParser = new AnnotationParserImpl();
        $this->addAvailableAnnotation('Panda\Core\Component\Router\Annotation\RequestMappingAnnotation',
            'RequestMapping');
    }

    public function addAvailableAnnotation($className, $shortName)
    {
        $this->annotationParser->addKnownAnnotation($className, $shortName);
    }

    public function reloadRoutes($reloadCache = false)
    {
        if (empty($routes) || $reloadCache) {
            $bundleControllers = glob(APP_DIR . '*Bundle/*BundleController.php');

            foreach ($bundleControllers as $controller) {
                $tags = $this->annotationParser->parse(str_replace('/', '\\', str_replace('.php', '',
                    str_replace(APP_DIR, APP_NAMESPACE . '\\', $controller))));

                foreach ($tags as $tag) {
                    $this->addRoute($tag->getValue(), $tag->getBundle(), $tag->getAction(), $tag->getMethod(),
                        $tag->getVars());
                }
            }
        }
    }

    public function addRoute($pattern, $bundle, $action, $httpMethod, $vars)
    {
        $this->routes[] = new Route($pattern, $bundle, $action, $httpMethod, $vars);
    }

    public function findMatchingRoute($url)
    {
        $badMethod = false;

        foreach ($this->routes as $route) {
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

    public function getRoutes()
    {
        return $this->routes;
    }
} 