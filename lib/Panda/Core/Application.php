<?php

namespace Panda\Core;

use Logger;
use Panda\Core\Component\Bundle\View\ViewFacade;
use Panda\Core\Component\Config\ConfigManager;
use Panda\Core\Component\Router\Exception\NoMatchingRouteException;
use Panda\Core\Component\Router\Exception\NoMatchingRouteMethodException;
use Panda\Core\Component\Router\Route;
use Panda\Core\Event\Observable;
use Panda\Core\Event\ObservableImpl;
use Panda\Core\Event\Observer;
use Panda\Core\Interceptor\HandlerInterceptor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The main class of panda-core
 * @package Panda\Core
 */
class Application extends ObservableImpl implements \ArrayAccess
{
    private $dependencies = array(
        'Tool/Function/String.php'
    );
    private $name;
    private $interceptors = array();
    private $services = array();
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
        $this->loadServices();
        $this->loadInterceptors();
    }

    /**
     * Run the application
     */
    public function run()
    {
        $router = $this->getComponent('Router\Router');
        $this->components['Symfony\Request'] = Request::createFromGlobals();
        $this->components['Symfony\Response'] = Response::create();

        try {
            $this->route = $router->findMatchingRoute(str_replace(WEB_ROOT, '/',
                $this['Symfony\Request']->getRequestUri()));
        } catch (NoMatchingRouteException $e) {
            //No matching route -> 404 Not Found
            $this->exitFailure(404);
        } catch (NoMatchingRouteMethodException $e) {
            $this->exitFailure(405);
        }

        //Notify interceptors
        foreach ($this->interceptors as $interceptor) {
            if (!$interceptor->preHandle($this['Symfony\Request'], $this->components['Symfony\Response'])) {
                $this->logger->debug('Interceptor preHandle() interrupts normal process.');
                exit;
            }
        }

        $view = $this->getController($this->route)->exec();

        //Notify interceptors
        foreach ($this->interceptors as $interceptor) {
            if (!$interceptor->postHandle($this['Symfony\Request'], $this->components['Symfony\Response'], $view)) {
                $this->logger->debug('Interceptor postHandle() interrupts normal process.');
                exit;
            }
        }

        if ($view->getHttpCode() >= 200 && $view->getHttpCode() <= 226) {
            $this->exitSuccess($view);
        } else {
            $this->exitFailure($view->getHttpCode());
        }
    }

    public function exitFailure($httpCode)
    {
        $this->logger->debug('Exit failure with HTTP code "'.$httpCode.'".');
        $view = new ViewFacade($this->components['Symfony\Response']);
        $view->setHttpCode($httpCode);

        $view->render();

        $this->components['Symfony\Response']->prepare($this->components['Symfony\Request']);
        $this->components['Symfony\Response']->send();
        exit;
    }

    public function exitSuccess(ViewFacade $view)
    {
        if (ConfigManager::configHasChanged()) {
            ConfigManager::saveAll();
            $this->logger->info('Config saved".');
        }

        foreach ($this->interceptors as $interceptor) {
            if (!$interceptor->afterCompletition($this['Symfony\Request'], $this->components['Symfony\Response'])) {
                $this->logger->debug('Interceptor afterCompletition() interrupts normal process.');
                exit;
            }
        }

        $execTime = microtime(true) - $this->startupTime;
        $this->logger->debug('Exit success with HTTP code "'.$view->getHttpCode().'" in '.$execTime.' s.');
        $this->components['Symfony\Response']->prepare($this->components['Symfony\Request']);
        $this->components['Symfony\Response']->send();
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
        $this->notify();
    }

    public function bindInterceptor(HandlerInterceptor $interceptor)
    {
        if (in_array($interceptor, $this->interceptors)) {
            throw new \RuntimeException('Already bind interceptor.');
        }
        $this->interceptors[] = $interceptor;
    }

    private function loadDependencies()
    {
        Autoloader::register();
        foreach ($this->dependencies as $dependency) {
            require_once $dependency;
        }
    }

    private function loadServices()
    {
        $this->logger->debug('Loading services.');
        $services = ConfigManager::get('services');

        if (!empty($services)) {
            foreach ($services as $serviceName => $config) {
                if (array_key_exists('className', $config) && !class_exists($config['className'])) {
                    throw new \RuntimeException('Unable to load "'.$serviceName.'" service: class not found.');
                }
                $reflectionClass = new \ReflectionClass($config['className']);
                if (!$reflectionClass->implementsInterface('Panda\\Core\\Service\\Service')) {
                    throw new \RuntimeException('Unable to load "'.$serviceName.'" service: class doesn\'t implement
                    Service interface.');
                }
                unset($config['className']);
                $this->services[$serviceName] = $reflectionClass->newInstanceArgs($config);
            }
        }
    }

    private function loadInterceptors()
    {
        $this->logger->debug('Loading interceptors.');
        $interceptors = ConfigManager::get('interceptors');

        if (!empty($interceptors)) {
            foreach ($interceptors as $interceptorName => $config) {
                if (array_key_exists('className', $config) && !class_exists($config['className'])) {
                    throw new \RuntimeException('Unable to load "'.$interceptorName.'" service: class not found.');
                }
                $reflectionClass = new \ReflectionClass($config['className']);
                if (!$reflectionClass->implementsInterface('Panda\\Core\\Interceptor\\HandlerInterceptor')) {
                    throw new \RuntimeException('Unable to load "'.$interceptorName.'" interceptor: class doesn\'t
                    implement HandlerInterceptor interface.');
                }
                unset($config['className']);
                $this->services[$interceptorName] = $reflectionClass->newInstanceArgs($config);
            }
        }
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