<?php

namespace Panda\Core\Component\Bundle;

use Logger;
use Panda\Core\Application;
use Panda\Core\Component\Bundle\View\ViewFacade;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractController implements Controller
{

    protected $app;
    protected $bundleName;
    protected $actionName;
    protected $view;
    protected $daos = array();
    protected $logger = null;

    public function __construct(Application $app, $bundleName, $actionName)
    {
        $this->logger = Logger::getLogger(__CLASS__);
        $this->logger->debug('Controller "'.$bundleName.'Controller" started.');
        $this->app = $app;
        $this->bundleName = $bundleName;
        $this->actionName = $actionName;
        $this->view = new ViewFacade($app->getComponent('Symfony\Response'));
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
                $response = new RedirectResponse(substr($viewName, strlen('redirect:')));
                $this->app['Symfony\Response']->prepare($this->app['Symfony\Request']);
                $response->send();
            } else {
                $this->view->render(BUNDLES_DIR . $this->bundleName . '/view/' . $viewName);
            }
        }

        return $this->view;
    }

    public function getDao($daoName, $bundleName = null)
    {
        if (!array_key_exists($daoName, $this->daos)) {
            if ($bundleName === null) {
                $daoClass = APP_NAMESPACE . '\\' . $this->bundleName . '\\' . $daoName . 'Dao';
            } else {
                if (!is_file(APP_DIR . $bundleName . '/' . $daoName . 'Dao.class.php')) {
                    throw new \InvalidArgumentException('Unknown Dao "'.$daoName.'"');
                }
                $daoClass = APP_NAMESPACE . '\\' . $bundleName . '\\' . $daoName . 'Dao';
            }
            $this->daos[$daoName] = new $daoClass();
        }
        return $this->daos[$daoName];
    }
}