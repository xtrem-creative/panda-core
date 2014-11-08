<?php

namespace Panda\Core;
use Logger;
use Panda\Core\Component\Router\Exception\NoMatchingRouteException;
use Symfony\Component\HttpFoundation\Request;

/**
 * The main class of panda-core
 * @package Panda\Core
 */
class Application implements \ArrayAccess
{
    private $name;
    private $components = array();

    public function __construct($name = 'prod')
    {
        $this->components['Logger\Logger'] = Logger::getLogger('panda');
        $this['Logger\Logger']->debug('Panda framework started.');
        $this->setName($name);
    }

    public function run()
    {
        $router = $this->getComponent('Router\Router');
        $this->components['Symfony\Request'] = Request::createFromGlobals();

        try {
            $matchingRoute = $router->findMatchingRoute(str_replace(WEB_ROOT, '/',
                $this['Symfony\Request']->getRequestUri()));
        } catch (NoMatchingRouteException $e) {
            //No matching route -> 404 Not Found
            $this->exitFailure(404);
        }
    }

    public function exitFailure($httpCode)
    {
        $this['Logger\Logger']->debug('Exit failure with HTTP code "'.$httpCode.'".');
        exit;
    }

    public function exitSuccess($httpCode)
    {
        $this['Logger\Logger']->debug('Exit success with HTTP code "'.$httpCode.'".');
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