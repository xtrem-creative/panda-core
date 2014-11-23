<?php

namespace Panda\Core\Component\Router\Provider\Annotation;

use Panda\Core\Tool\Annotation\Annotation;
use ReflectionMethod;

class RequestMappingAnnotation implements Annotation
{
    private $value;
    private $method;
    private $namespace;
    private $bundleName;
    private $action;
    private $vars;

    public function __construct(ReflectionMethod $classMethod, $value, $httpMethod = 'GET|POST')
    {
        $this->value = $value;
        $this->method = explode('|', $httpMethod);
        $this->action = $classMethod->getName();
        $this->namespace = $classMethod->getDeclaringClass()->getNamespaceName();
        $this->bundleName = substr(substr($classMethod->getDeclaringClass()->getName(), strlen($this->namespace) + 1)
            , 0, -10);//10 = strlen('Controller')
        $this->vars = array_map(function($p){
            return array('name' => $p->getName(), 'required' => !$p->isOptional());
        }, $classMethod->getParameters());
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
     * @return array
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param array $method
     */
    public function setMethod(array $method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
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
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param array $vars
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
    }


} 