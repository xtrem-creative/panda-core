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
    private $securedCriteras;

    public function __construct($urlPattern, $bundleName, $actionName, $securedCriteras = null)
    {
        $this->setUrlPattern($urlPattern);
        $this->setBundleName($bundleName);
        $this->setActionName($actionName);
        $this->setSecuredCriteras($securedCriteras);
    }

    public function match($url)
    {

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
    public function getSecuredCriteras()
    {
        return $this->securedCriteras;
    }

    /**
     * @param mixed $securedCriteras
     */
    public function setSecuredCriteras($securedCriteras)
    {
        $this->securedCriteras = $securedCriteras;
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
} 