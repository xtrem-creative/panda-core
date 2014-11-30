<?php

namespace Panda\Core\Component\Bundle;

use Logger;
use Panda\Core\Application;
use Panda\Core\Component\Bundle\View\ViewFacade;
use Panda\Core\Event\ObservableImpl;
use Panda\Core\Loggable;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractController extends ObservableImpl implements Controller
{
    protected $app;
    protected $namespace;
    protected $bundleName;
    protected $actionName;
    protected $view;
    protected $daos = array();

    public function __construct(Application $app, $namespace, $bundleName, $actionName)
    {
        $this->init(__CLASS__);
        $this->logger->debug('Controller "'.$bundleName.'Controller" started.');
        $this->app = $app;
        $this->namespace = $namespace;
        $this->bundleName = $bundleName;
        $this->actionName = $actionName;
        $this->view = new ViewFacade($app->getComponent('Symfony\Request'), $app->getComponent('Symfony\Response'));
        $this->notify();
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
            if (str_starts_with($viewName, 'redirect:')) {
                $dest = substr($viewName, strlen('redirect:'));
                if ($dest[0] = '/') {
                    $dest = WEB_ROOT . substr($dest, 1);
                }
                $response = new RedirectResponse($dest);
                $this->app['Symfony\Response']->prepare($this->app['Symfony\Request']);
                $response->send();
            } else {
                $reflClass = new ReflectionClass($this);
                $this->view->setViewPath(dirname($reflClass->getFileName()) . '/view/' . $viewName);
            }
        }

        return $this->view;
    }

    public function getDao($daoName, $bundleName = null)
    {
        if (!array_key_exists($daoName, $this->daos)) {
            if ($bundleName === null) {
                $daoClass = $this->namespace . '\\dao\\' . $daoName . 'Dao';
            } else {
                if (!is_file(APP_DIR . $bundleName . '/' . $daoName . 'Dao.class.php')) {
                    throw new \InvalidArgumentException('Unknown Dao "'.$daoName.'"');
                }
                $daoClass = $this->namespace . '\\dao\\' . $daoName . 'Dao';
            }
            $this->daos[$daoName] = new $daoClass();
        }
        return $this->daos[$daoName];
    }
}