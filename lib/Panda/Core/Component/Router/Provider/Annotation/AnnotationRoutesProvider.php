<?php

namespace Panda\Core\Component\Router\Provider\Annotation;

use Panda\Core\Component\Router\Provider\AbstractRoutesProvider;
use Panda\Core\Tool\Annotation\AnnotationParserImpl;

class AnnotationRoutesProvider extends AbstractRoutesProvider
{
    private $annotationParser;

    public function __construct()
    {
        $this->annotationParser = new AnnotationParserImpl();
        $this->addAvailableAnnotation('Panda\Core\Component\Router\Provider\Annotation\RequestMappingAnnotation',
            'RequestMapping');
    }

    public function reloadRoutes($reloadCache = false)
    {
        if ($reloadCache || empty($this->routes)) {
            $bundleControllers = glob(BUNDLES_DIR . '*Bundle/*BundleController.php');
            foreach ($bundleControllers as $controller) {
                $tags = $this->annotationParser->parse(str_replace('/', '\\', str_replace('.php', '',
                    str_replace(BUNDLES_DIR, APP_NAMESPACE . '\\', $controller))));

                foreach ($tags as $tag) {
                    if ($tag instanceof RequestMappingAnnotation) {
                        $this->addRoute($tag->getValue(), $tag->getBundle(), $tag->getAction(), $tag->getMethod(),
                            $tag->getVars());
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