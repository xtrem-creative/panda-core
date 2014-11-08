<?php

namespace Panda\Core\Tool\Annotation;

use ReflectionClass;

class AnnotationParserImpl implements AnnotationParser
{
    public function parse($class)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException('The class "'.$class.'" doesn\'t exists.');
        }

        $c = new ReflectionClass($class);
        $methodsList = $c->getMethods();

        foreach ($methodsList as $m) {
            $s = $m->getDocComment();
            $s = str_replace('/*', '', $s);
            $s = str_replace('*/', '', $s);
            $s = str_replace('*', '', $s);
            $aTags = explode('@', $s);
            echo '<pre>';
            var_dump($aTags);
            echo '</pre><br />';
        }
    }

    public function getKnownAnnotations()
    {
        // TODO: Implement getKnownAnnotations() method.
    }

    public function addKnownAnnotation(Annotation $a)
    {
        // TODO: Implement addKnownAnnotation() method.
    }
}