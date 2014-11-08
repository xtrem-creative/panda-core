<?php

namespace Panda\Core\Component\Router\Annotation;

use Panda\Core\Tool\Annotation\Annotation;
use ReflectionMethod;

class RequestMappingAnnotation implements Annotation
{
    private $value;
    private $method;
    private $bundle;
    private $action;
    private $vars;

    public function __construct($value, $httpMethod, ReflectionMethod $classMethod)
    {
        $this->value = $value;
        $this->method = $httpMethod;
        $this->action = $classMethod->getName();
        $this->bundle = str_replace('Controller', '', $classMethod->class);

        $this->vars = array_map(function($p){return $p->getName();}, $classMethod->getParameters());
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param mixed $bundle
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return \ReflectionParameter[]
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param \ReflectionParameter[] $vars
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
    }
} 