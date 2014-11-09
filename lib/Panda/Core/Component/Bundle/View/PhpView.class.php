<?php

namespace Panda\Core\Component\Bundle\View;

use Logger;

class PhpView implements View
{
    private $logger;

    public function render($templateName, $vars = null)
    {
        $this->logger = Logger::getLogger(__CLASS__);
        $this->logger->debug('Render "'.$templateName.'" with raw PHP engine');
    }
}