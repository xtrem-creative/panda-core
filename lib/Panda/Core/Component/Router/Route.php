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

    /**
     * Check whether the given url matches with one in the known patterns list.
     * @param $url
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function match($url)
    {
        $urlComponents = parse_url($url);
        if (false !== $urlComponents) {
            if (preg_match('`^' . $this->urlPattern . '$`', $urlComponents['path'], $matches)) {
                if (!empty($this->vars)) {
                    unset($matches[0]);
                    $matches = array_values($matches);

                    $varsCount = count($this->vars);
                    $matchesCount = count($matches);

                    $vars = array();
                    $i = 0;

                    foreach ($this->vars as $varValue) {
                        if ($i < $matchesCount) {
                            $vars[$this->vars[$i]['name']] = $varValue;
                        } else {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && array_key_exists($this->vars[$i]['name'],
                                    $_POST)) {
                                $vars[$this->vars[$i]['name']] = $_POST[$this->vars[$i]['name']];
                            } else if ($_SERVER['REQUEST_METHOD'] === 'POST' && array_key_exists($this->vars[$i]['name'],
                                    $_FILES)) {
                                $vars[$this->vars[$i]['name']] = $_FILES[$this->vars[$i]['name']];
                            } else if (array_key_exists($this->vars[$i]['name'], $_GET)) {
                                $vars[$this->vars[$i]['name']] = $_GET[$this->vars[$i]['name']];
                            } else if($this->vars[$i]['required']) {
                                throw new \InvalidArgumentException('Unknown parameter value for "'.
                                    $this->vars[$i]['name'].'"');
                            }
                        }
                        ++$i;
                    }

                    $this->vars = $vars;
                }
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @param string $actionName
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * @return string
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }

    /**
     * @param string $bundleName
     */
    public function setBundleName($bundleName)
    {
        $this->bundleName = $bundleName;
    }

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * @param string $httpMethod
     */
    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = is_array($httpMethod) ? $httpMethod : array($httpMethod);
    }

    /**
     * @return string
     */
    public function getUrlPattern()
    {
        return $this->urlPattern;
    }

    /**
     * @param string $urlPattern
     */
    public function setUrlPattern($urlPattern)
    {
        $this->urlPattern = $urlPattern;
    }

    /**
     * @return string
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param string $vars
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
    }
} 