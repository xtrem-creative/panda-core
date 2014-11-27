<?php

namespace Panda\Core\Component\Bundle\View\Resolver;

use Logger;
use Panda\Core\Component\Bundle\View\View;

class PhpView extends AbstractViewResolver
{
    private $logger;

    public function render($templateName, $vars = null)
    {
        $this->logger = Logger::getLogger(__CLASS__);
        $this->logger->debug('Render "'.$templateName.'" with raw PHP engine');
        $vars['webroot'] = WEB_ROOT;
        $vars['currentUrl'] = $this->viewFacade->getRequest()->getRequestUri();

        ob_start();
        extract($vars, EXTR_PREFIX_ALL, 'v');
        include $templateName;

        $result = ob_get_clean();

        $this->logger->debug('Render "'.$templateName.'": done');

        return $result;
    }
}