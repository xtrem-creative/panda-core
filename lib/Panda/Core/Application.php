<?php

namespace Panda\Core;

use Logger;
use Panda\Core\Component\Bundle\View\ViewFacade;
use Panda\Core\Component\Config\ConfigManager;
use Panda\Core\Component\Router\Exception\NoMatchingRouteException;
use Panda\Core\Component\Router\Exception\NoMatchingRouteMethodException;
use Panda\Core\Component\Router\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The main class of panda-core
 * @package Panda\Core
 */
class Application implements \ArrayAccess
{
    private $dependencies = array(
        'Tool/Function/String.php'
    );
    private $name;
    private $components = array();
    private $logger = null;
    private $route;
    private $startupTime;

    public function __construct($name = 'prod')
    {
        $this->startupTime = microtime(true);
        $this->loadDependencies();
        Logger::configure(RESOURCES_DIR . 'config/log4php.xml');
        $this->logger = Logger::getLogger(__CLASS__);
        $this->logger->debug('Panda framework started.');
        $this->setName($name);
    }

    public function loadDependencies()
    {
        Autoloader::register();
        foreach ($this->dependencies as $dependency) {
            require_once $dependency;
        }
    }

    public function run()
    {
        $router = $this->getComponent('Router\Router');
        $router->reloadRoutes();
        $this->components['Symfony\Request'] = Request::createFromGlobals();

        try {
            $this->route = $router->findMatchingRoute(str_replace(WEB_ROOT, '/',
                $this['Symfony\Request']->getRequestUri()));
        } catch (NoMatchingRouteException $e) {
            //No matching route -> 404 Not Found
            $this->exitFailure(404);
        } catch (NoMatchingRouteMethodException $e) {
            $this->exitFailure(405);
        }

        $view = $this->getController($this->route)->exec();

        if ($view->getHttpCode() >= 200 && $view->getHttpCode() <= 226) {
            $this->exitSuccess($view);
        } else {
            $this->exitFailure($view->getHttpCode());
        }
    }

    public function exitFailure($httpCode)
    {
        $this->logger->debug('Exit failure with HTTP code "'.$httpCode.'".');
        //TODO! Display error
        echo 'Error:' . $httpCode;
        exit;
    }

    public function exitSuccess(ViewFacade $view)
    {
        if (ConfigManager::configHasChanged()) {
            ConfigManager::saveAll();
            $this->logger->info('Config saved".');
        }
        $response = new Response(
            $view->getRenderedContent(),
            $view->getHttpCode(),
            array('content-type', $view->getContentType())
        );
        $execTime = microtime(true) - $this->startupTime;
        $this->logger->debug('Exit success with HTTP code "'.$view->getHttpCode().'" in '.$execTime.' s.');
        $response->send();
        exit;
    }

    public function getComponent($componentName)
    {
        if (!array_key_exists($componentName, $this->components)) {
            $componentClassPath = 'Panda\\Core\\Component\\' . $componentName;
            $this->components[$componentName] = new $componentClassPath();
        }
        return $this->components[$componentName];
    }

    public function getController(Route $route)
    {
        $controllerClass = APP_NAMESPACE . '\\' . $route->getBundleName() . '\\' . $route->getBundleName() .
            'Controller';

        return new $controllerClass($this, $route->getBundleName(), $route->getActionName());
    }

    /**
     * Get the application name
     * @return string The application name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the application name
     * @param string $name The application name
     * @throws \InvalidArgumentException
     */
    public function setName($name)
    {
        if (is_string($name) && !empty($name)) {
            $this->name = $name;
        } else {
            throw new \InvalidArgumentException('Invalid application name "'.$name.'"');
        }
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @throws \RuntimeException
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        throw new \RuntimeException('Unable to check the existance of a component.');
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getComponent($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @throws \RuntimeException
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('Unable to set a component.');
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @throws \RuntimeException
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException('Unable to delete a component.');
    }
}