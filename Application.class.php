<?php

/**
 * Panda application
 * 
 * This is the main part of the Panda framework. Here we'll check if a route exists
 * for the current url, and execute the matching controller. Each functionality is
 * seen as a component, and can be loaded from the application. 
 * 
 * @package Panda
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */

namespace Panda;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Panda\component\router\Route;
use Panda\component\error\ErrorHandler;
use Panda\component\bundle\AbstractController;

class Application
{
	protected $components = array();
    protected $services = array();
    protected $route;

    public function __construct()
    {
        $this->components['error\ErrorHandler'] = new ErrorHandler($this);
    }

    public function getService($serviceName)
    {
        if (array_key_exists($serviceName, $this->services)) {
            return $this->services[$serviceName];
        } else {
            throw new \RuntimeException('Unknown service "'.(string) $serviceName.'"');
        }
    }

    /**
     * Gets the application route
     * 
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Gets the controller matching with the given route
     * 
     * @param Route $route
     * @return Controller The matching controller, if it exists
     * @throws RuntimeException
     */
    public function getController(Route $route = null)
    {
        if (APP_DIR . $route->getBundleName() . '/' . $route->getBundleName() . 'Controller.class.php') {
            $controllerPath = $route->getBundleName() . '\\' . $route->getBundleName() . 'Controller';
            return new $controllerPath($this, $route->getBundleName(), $route->getActionName());
        } else {
            throw new \RuntimeException('Unknown controller for "'.$route->getBundleName().'"');
        }
    }

    public function getRequest()
    {
        return $this->components['symfony\request'];
    }

    /**
     * Runs the current application
     */
    public function run()
    {
        $this->components['symfony\request'] = Request::createFromGlobals();
        $router = $this->getComponent('router\Router');

        try {
            $this->route = $router->getMatchingRoute(str_replace(WEB_ROOT, '/', $this->getComponent('symfony\request')->getRequestUri()));
        } catch (\RuntimeException $e) {
            $this->emergencyExit(404);
        }

        $this->_loadServices();
        $accessFilterResult = $this->accessFilter();

        if ($accessFilterResult === false) {
            $fallbackUrl = $this->route->getFallback();
            if ($fallbackUrl === null) {
                $this->emergencyExit(403);
            } else {
                $this->redirect($fallbackUrl);
            }
        } else {
            if ($accessFilterResult instanceof AbstractController) {
                $accessFilterResult->exec();
            } else {
                $this->getController($this->route)->exec();
            }
        }
    }

    /**
     * Checks if the current user is able to access to the current action
     * 
     * @return bool
     */
    public function accessFilter()
    {
        $currentFilters = $this->getRoute()->getFilters();
        if ($currentFilters === NULL) {
            return true;
        } else {
            if ($currentFilters !== 'ROLE_GUEST') {
                if (!$this->getService('user')->isOnline()) {
                    $controllerPath = 'UserBundle\UserBundleController';
                    return new $controllerPath($this, 'UserBundle', 'login');
                } else if ($this->getService('user')->hasRoles($currentFilters)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return !$this->getService('user')->isOnline();
            }
        }
    }

    private function _loadServices()
    {
        $services = $this->getComponent('config\Config')->get('services');
        if ($services !== null) {
            foreach ($services as $key => $service) {
                if (!array_key_exists('class', $service)) {
                    throw new \RuntimeException('Can\'t load the service "'.$key.'"');
                }
                $this->services[$key] = new $service['class']($this);
            }
        }
    }

    /**
     * Redirects the user to $url
     * 
     * @param string $url
     * @param int $httpCode 301 to make a temporary redirection, 302 for a permanent one
     */
    public function redirect($url, $httpCode = 301)
    {
        $redirectResponse = new RedirectResponse($url, $httpCode);
        exit($redirectResponse->send());
    }

    /**
     * Gets an application component
     * 
     * @param string $componentName
     * @return Component
     */
    public function getComponent($componentName)
    {
    	if (!array_key_exists($componentName, $this->components)) {
    		$componentPath = '\\Panda\\component\\' . $componentName;
            if (func_num_args() > 1) {
                $reflection = new \ReflectionClass($componentPath);
                $args = func_get_args();
                unset($args[0]);
                $this->components[$componentName] = $reflection->newInstanceArgs(array_merge(array($this), $args));
            } else {
                $this->components[$componentName] = new $componentPath($this);   
            }
    	}
    	return $this->components[$componentName];
    }

    /**
     * Exits the application in emergency cases
     * 
     * @param int $code The HTTP code to return
     */
    public function emergencyExit($code)
    {
        $view = $this->getComponent('bundle\View');
        $view->setHttpCode($code);
        $response = new Response(
            $view->render(null),
            $code,
            array('content-type' => 'text/html')
        );
        $response->prepare($this->getComponent('symfony\request'));
        $response->send();
        exit;
    }

    /**
     * Exits the application in success cases
     * 
     * @param string $content
     * @param int $httpCode
     * @param string $contentType
     */
    public function successExit($content, $httpCode, $contentType)
    {
        $response = new Response(
            $content,
            $httpCode,
            array('content-type' => $contentType)
        );
        $response->prepare($this->getComponent('symfony\request'));
        $response->send();
        exit;
    }
}