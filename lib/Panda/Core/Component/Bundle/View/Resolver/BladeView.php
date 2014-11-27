<?php

namespace Panda\Core\Component\Bundle\View\Resolver;

use Logger;
use Panda\Core\Component\Bundle\View\Exception\ResourceNotFoundException;
use Panda\Core\Component\Bundle\View\Exception\ResourceNotWritableException;
use Panda\Core\Component\Bundle\View\View;
use Philo\Blade\Blade;

class BladeView extends AbstractViewResolver
{
    private $logger;
    protected $templatesDir = null;
    protected $viewsDir = null;
    protected $cacheDir = null;

    public function __construct($viewFacade, $templatesDir, $viewsDir, $cacheDir)
    {
        parent::__construct($viewFacade);
        $this->setTemplatesDir($templatesDir);
        $this->setViewsDir($viewsDir);
        $this->setCacheDir($cacheDir);
    }

    public function render($templateName, $vars = null)
    {
        $this->logger = Logger::getLogger(__CLASS__);

        $blade = new Blade(array($this->templatesDir, $this->viewsDir), $this->cacheDir);

        $blade->view()->share('webroot', WEB_ROOT);
        $blade->view()->share('currentUrl', $this->viewFacade->getRequest()->getRequestUri());

        $result = $blade->view()->make(basename($templateName, '.blade.php'), $vars);

        $this->logger->debug('Render "'.$templateName.'" with Blade engine');

        return $result;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @param string $cacheDir
     * @throws \Panda\Core\Component\Bundle\View\Exception\ResourceNotFoundException
     * @throws \Panda\Core\Component\Bundle\View\Exception\ResourceNotWritableException
     */
    public function setCacheDir($cacheDir)
    {
        if (is_dir($cacheDir)) {
            if (is_writable($cacheDir)) {
                if (!file_exists($cacheDir . 'blade')) {
                    mkdir($cacheDir . 'blade');
                }
                $this->cacheDir = $cacheDir . 'blade';
            } else {
                throw new ResourceNotWritableException('Blade "'.$cacheDir.'" cache directory is not writable. Please
                check the permissions for this folder');
            }
        } else {
            throw new ResourceNotFoundException('Blade "'.$cacheDir.'" cache directory not found');
        }
    }

    /**
     * @return null
     */
    public function getTemplatesDir()
    {
        return $this->templatesDir;
    }

    /**
     * @param string $templatesDir
     * @throws \Panda\Core\Component\Bundle\View\Exception\ResourceNotFoundException
     */
    public function setTemplatesDir($templatesDir)
    {
        if (is_dir($templatesDir)) {
            $this->templatesDir = $templatesDir;
        } else {
            throw new ResourceNotFoundException('Blade "'.$templatesDir.'" templates directory not found');
        }
    }

    /**
     * @return null
     */
    public function getViewsDir()
    {
        return $this->viewsDir;
    }

    /**
     * @param string $viewsDir
     * @throws ResourceNotFoundException
     */
    public function setViewsDir($viewsDir)
    {
        if (is_dir($viewsDir)) {
            $this->viewsDir = $viewsDir;
        } else {
            throw new ResourceNotFoundException('Twig "'.$viewsDir.'" views directory not found');
        }
    }
}