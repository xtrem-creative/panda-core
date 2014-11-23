<?php

namespace Panda\Core\Component\Router\Provider\Annotation;

use Panda\Core\Component\Router\Provider\AbstractRoutesProvider;
use Panda\Core\Tool\Annotation\AnnotationParserImpl;

class AnnotationRoutesProvider extends AbstractRoutesProvider
{
    private $annotationParser;

    public function __construct(array $bundles)
    {
        $this->annotationParser = new AnnotationParserImpl();
        $this->addAvailableAnnotation('Panda\Core\Component\Router\Provider\Annotation\RequestMappingAnnotation',
            'RequestMapping');
        $this->bundles = $bundles;
    }

    public function reloadRoutes($reloadCache = false)
    {
        if ($reloadCache || empty($this->routes)) {
            foreach ($this->bundles as $bundle => $controller) {
                $tags = $this->annotationParser->parse($controller->getName());

                foreach ($tags as $tag) {
                    if ($tag instanceof RequestMappingAnnotation) {
                        $this->addRoute($tag->getValue(), $tag->getNamespace(), $tag->getBundleName(),
                            $tag->getAction(), $tag->getMethod(), $tag->getVars());
                    }
                }
            }
            $this->saveCache();
        } else {
            $this->loadCache();
        }
    }

    public function addAvailableAnnotation($className, $shortName)
    {
        $this->annotationParser->addKnownAnnotation($className, $shortName);
    }
}