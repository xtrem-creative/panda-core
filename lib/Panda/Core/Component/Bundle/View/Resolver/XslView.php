<?php

namespace Panda\Core\Component\Bundle\View\Resolver;

use Logger;
use Panda\Core\Component\Bundle\View\View;
use Panda\Core\Tool\Xml\DOMDocument;

class XslView implements View
{
    private $logger;

    public function __construct()
    {

    }

    public function render($templateName, $vars = null)
    {
        $this->logger = Logger::getLogger(__CLASS__);
        $this->logger->debug('Render "'.$templateName.'" with XSLT engine');

        $xml = new DOMDocument();
        $xml->fromMixed($vars);

        $xsl = new DOMDocument();
        $xsl->load($templateName);

        $proc = new \XSLTProcessor();
        $proc->importStyleSheet($xsl);

        $result = $proc->transformToXML($xml);

        $this->logger->debug('Render "'.$templateName.'": done');

        return $result;
    }
}