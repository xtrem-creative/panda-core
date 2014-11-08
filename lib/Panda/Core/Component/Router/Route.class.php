<?php

namespace Panda\Core\Component\Router;

/**
 * A class to represent a route to a ressource
 * @package Panda\Core\Component\Router
 */
class Route
{
    private $urlPattern;
    private $bundleName;
    private $actionName;
    private $httpMethod;
    private $vars;

    public function __construct($urlPattern, $bundleName, $actionName, $httpMethod, $vars)
    {
        $this->setUrlPattern($urlPattern);
        $this->setBundleName($bundleName);
        $this->setActionName($actionName);
        $this->setHttpMethod($httpMethod);
        $this->setVars($vars);
    }

    public function match($url)
    {
        $matches = array();
        if (preg_match('`^' . $this->urlPattern . '$`', $url, $matches)) {
            if (!empty($this->vars)) {
                unset($matches[0]);
                $matches = array_values($matches);
                $this->vars = array_combine($this->vars, $matches);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @param mixed $actionName
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * @return mixed
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }

    /**
     * @param mixed $bundleName
     */
    public function setBundleName($bundleName)
    {
        $this->bundleName = $bundleName;
    }

    /**
     * @return mixed
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * @param mixed $httpMethod
     */
    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = is_array($httpMethod) ? $httpMethod : array($httpMethod);
    }

    /**
     * @return mixed
     */
    public function getUrlPattern()
    {
        return $this->urlPattern;
    }

    /**
     * @param mixed $urlPattern
     */
    public function setUrlPattern($urlPattern)
    {
        $this->urlPattern = $urlPattern;
    }

    /**
     * @return mixed
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param mixed $vars
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
    }
} 