<?php

namespace Panda\Core\Tool\Annotation;

use Logger;
use ReflectionClass;

class AnnotationParserImpl implements AnnotationParser
{
    private static $phpdoc_tags = array(
        'package',
        'author',
        'param',
        'return'
    );
    private $knownTags = array();
    private $logger = null;

    public function __construct()
    {
        $this->logger = Logger::getLogger(__CLASS__);
    }

    public function parse($class)
    {
        $tagsList = array();

        if (!class_exists($class)) {
            throw new \InvalidArgumentException('The class "'.$class.'" doesn\'t exists.');
        }

        $c = new ReflectionClass($class);
        $methodsList = $c->getMethods();

        foreach ($methodsList as $m) {
            $tags = $this->extractTags($m);

            foreach ($tags as $tag) {
                $posParenthesis = strpos($tag, '(');
                if ($posParenthesis !== false) {
                    $tagName = substr($tag, 0, $posParenthesis);
                    $params = $this->extractParams(substr($tag, $posParenthesis + 1, -1));
                } else {
                    $tagName = $tag;
                    $params = array();
                }

                array_unshift($params, $m);

                if (array_key_exists($tagName, $this->knownTags)) {
                    $reflection = new ReflectionClass($this->knownTags[$tagName]);
                    $tagsList[] = $reflection->newInstanceArgs($params);
                } else if (!in_array($tagName, self::$phpdoc_tags)) {
                    $this->logger->info('Unkown annotation "'.$tagName.'" for class "'.$class.'"');
                }
            }
        }

        return $tagsList;
    }

    private function extractParams($params)
    {
        $paramsA = array_map('trim', explode(',', $params));
        $result = array();

        foreach ($paramsA as $param) {
            if (false !== strpos($param, '=')) {
                $expl = explode('=', $param);
                $result[] = trim($expl[1], '"');
            } else {
                $result[] = $param;
            }
        }

        return $result;
    }

    private function extractTags($method)
    {
        $s = $method->getDocComment();
        $s = str_replace('/*', '', $s);
        $s = str_replace('*/', '', $s);
        $s = str_replace('*', '', $s);
        $aTags = explode('@', $s);
        unset($aTags[0]);

        return array_map('trim', $aTags);
    }

    public function getKnownAnnotations()
    {
        return $this->knownTags;
    }

    public function addKnownAnnotation($annotationClassName, $alias = null)
    {
        $this->knownTags[$alias === null ? $annotationClassName : $alias] = $annotationClassName;
    }
}