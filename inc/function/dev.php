<?php

function debug($var)
{
   echo '<pre>';
   var_dump($var);
   echo '</pre>';
}

function get_method_argNames($class, $method)
{
    $m = new ReflectionMethod($class, $method);
    $result = array();
    foreach ($m->getParameters() as $param) {
        $result[] = $param->name;   
    }
    return $result;
}