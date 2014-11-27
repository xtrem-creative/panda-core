<?php

namespace Panda\Core\Component\Bundle\View\Resolver;

use Logger;
use Panda\Core\Component\Bundle\View\Exception\ResourceNotFoundException;
use Panda\Core\Component\Bundle\View\Exception\ResourceNotWritableException;
use Panda\Core\Component\Bundle\View\View;
use Panda\Core\Component\Bundle\View\ViewFacade;
use Twig_Environment;
use Twig_Loader_Filesystem;

class TwigView extends AbstractViewResolver
{
    protected $logger = null;
    protected $templatesDir = null;
    protected $viewsDir = null;
    protected $cacheDir = null;
    protected $devMode = true;

    public function __construct(ViewFacade $viewFacade, $templatesDir, $viewsDir, $cacheDir, $devMode = true)
    {
        parent::__construct($viewFacade);
        $this->setTemplatesDir($templatesDir);
        $this->setViewsDir($viewsDir);
        $this->setCacheDir($cacheDir);
        $this->setDevMode($devMode);
    }

    public function render($templateName, $vars = null)
    {
        $this->logger = Logger::getLogger(__CLASS__);
        $this->logger->debug('Render "'.$templateName.'" with Twig engine');

        $twigLoader = new Twig_Loader_Filesystem(array(
                $this->viewsDir,
                $this->templatesDir
            )
        );
        $twigLoader->addPath($this->templatesDir, 'layouts');

        $twig = new Twig_Environment($twigLoader, array(
            'cache' => $this->cacheDir,
            'debug' => $this->devMode,
            'auto_reload' => $this->devMode
        ));

        $twig->addGlobal('webroot', WEB_ROOT);
        $twig->addGlobal('currentUrl', $this->viewFacade->getRequest()->getRequestUri());

        $result = $twig->render(basename($templateName), $vars);

        $this->logger->debug('Render "'.$templateName.'": done');

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
                if (!file_exists($cacheDir . 'twig')) {
                    mkdir($cacheDir . 'twig');
                }
                $this->cacheDir = $cacheDir . 'twig';
            } else {
                throw new ResourceNotWritableException('Twig "'.$cacheDir.'" cache directory is not writable. Please
                check the permissions for this folder');
            }
        } else {
            throw new ResourceNotFoundException('Twig "'.$cacheDir.'" cache directory not found');
        }
    }

    /**
     * @return boolean
     */
    public function getDevMode()
    {
        return $this->devMode;
    }

    /**
     * @param boolean $devMode
     * @throws \InvalidArgumentException
     */
    public function setDevMode($devMode)
    {
        if (is_bool($devMode)) {
            $this->devMode = $devMode;
        } else {
            throw new \InvalidArgumentException('Invalid dev mode switch value for Twig engine.');
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
            throw new ResourceNotFoundException('Twig "'.$templatesDir.'" templates directory not found');
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
     * @throws \Panda\Core\Component\Bundle\View\Exception\ResourceNotFoundException
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