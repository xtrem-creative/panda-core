<?php

namespace Panda\Core;

use Logger;

/**
 * Class Loggable
 *
 * A core class which gives useful debugging tools.
 *
 * @package Panda\Core
 */
abstract class Loggable
{
    protected $startupTime;
    protected $logger = null;

    public function init($class)
    {
        $this->startupTime = microtime(true);
        $this->logger = Logger::getLogger($class);
    }

    public function getRunningTime()
    {
        return microtime(true) - $this->startupTime;
    }
}