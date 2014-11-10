<?php

namespace Panda\Core\Component\Router\Provider\File;

use Logger;
use Panda\Core\Component\Router\Provider\File\Attribute\BundleAttribute;
use Panda\Core\Component\Router\Provider\File\Attribute\UrlPatternAttribute;
use ReflectionClass;

/**
 * Class RouteFilesParser
 * A parser for routes config files. Checks whether the entries are correct
 * and gives a readable set of route attributes.
 * @package Panda\Core\Component\Router\Provider\File
 */
class RouteFilesParser
{
    private $knownAttributes = array();
    private $logger = null;
    private $routes = array();

    public function __construct()
    {
        $this->logger = Logger::getLogger(__CLASS__);
    }

    public function parse($routesFile)
    {
        $routesRaw = require_once $routesFile;
        $bundleName = str_replace('/res/config/routes.php', '', str_replace(APP_DIR, '', $routesFile));

        foreach ($routesRaw as $route => $config) {
            if (array_key_exists($route, $this->routes)) {
                throw new \RuntimeException('Duplicate route "'.$route.'" in "'.$bundleName.'"');
            } else {
                $attrList = array('urlPattern' => new UrlPatternAttribute($route));
                $attrList['bundle'] = new BundleAttribute($bundleName);

                foreach ($config as $configKey => $configValue) {
                    if (array_key_exists($configKey, $this->knownAttributes)) {
                        $reflection = new ReflectionClass($this->knownAttributes[$configKey]);
                        $attrList[$configKey] = $reflection->newInstanceArgs($configValue);
                    } else {
                        $this->logger->info('Unknown config key "'.$configKey.'" for route file "'.$routesFile.'"');
                    }
                }
                $this->routes[] = $attrList;
            }
        }

        return $this->routes;
    }

    public function getKnownAttributes()
    {
        return $this->knownAttributes;
    }

    public function addKnownAttribute($attributeClassName, $alias = null)
    {
        $this->knownAttributes[$alias === null ? $attributeClassName : $alias] = $attributeClassName;
    }
} 