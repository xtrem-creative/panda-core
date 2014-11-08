<?php

namespace Panda\Core\Tool\Annotation;

interface AnnotationParser
{
    public function parse($class);

    public function getKnownAnnotations();

    public function addKnownAnnotation($annotationClassName, $alias = null);
} 