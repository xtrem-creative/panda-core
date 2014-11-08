<?php

namespace Panda\Core\Component\Router\Annotation;

use Panda\Core\Tool\Annotation\Annotation;

class RequestMappingAnnotation implements Annotation
{
    public $value;
    public $method;
} 