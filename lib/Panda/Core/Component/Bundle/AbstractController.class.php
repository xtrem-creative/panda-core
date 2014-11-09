<?php

namespace Panda\Core\Component\Bundle;

use Logger;
use Panda\Core\Application;
use Panda\Core\Component\Bundle\View\ViewFacade;
use ReflectionClass;
use RuntimeException;

abstract class AbstractController implements Controller
{

    protected $app;
    protected $bundleName;
    protected $actionName;
    protected $view;
    protected $logger = null;

    public function __construct(Application $app, $bundleName, $actionName)
    {
        $this->logger = Logger::getLogger(__CLASS__);
        $this->logger->debug('Controller "'.$bundleName.'Controller" started.');
        $this->app = $app;
        $this->bundleName = $bundleName;
        $this->actionName = $actionName;
        $this->view = new ViewFacade();
    }

    public function exec()
    {
        $c = new ReflectionClass($this);

        if(!$c->hasMethod($this->actionName)) {
            throw new RuntimeException('"' . $this->actionName . '" action isn\'t defined for this module.');
        }

        $method = $c->getMethod($this->actionName);
        $viewName = $method->invokeArgs($this, $this->app->getRoute()->getVars());

        if ($viewName !== null) {
            $this->view->render(APP_DIR . $this->bundleName . '/view/' . $viewName);
        }

        return $this->view;
    }

    public function getDao($daoName)
    {
        // TODO: Implement getDao() method.
    }
}