<?php

/**
 * Panda controller
 * 
 * A bundle is an application brick : it contains a Controller, eventually a Model
 * some views and ressources. The controller is part of the MVC pattern, and its role
 * is to manage the logical part of the bundle.
 * 
 * @package Panda
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */

namespace Panda\component\bundle;

use Panda\component\AbstractComponent;
use Panda\Application;

abstract class AbstractController extends AbstractComponent
{
	protected $bundleName;
	protected $actionName;
	protected $view;
    protected $models = array();

    /**
     * Constructs a controller
     * 
     * @param Panda\Application $app
     * @param string $bundleName
     * @param string $actionName
     */
	public function __construct(Application $app, $bundleName, $actionName)
	{
		parent::__construct($app);
		$this->setBundleName($bundleName);
		$this->setActionName($actionName);
		$this->view = new View($app);
		$this->view->setBundleName($bundleName);
	}

	/**
	 * Executes the current controller
	 * 
	 * @throws RuntimeException
	 */
	public function exec()
	{
		if (!is_callable(array($this, $this->actionName . 'Action'))) {
			throw new \RuntimeException('"' . $this->actionName . '" action isn\'t defined for this module.');
		}
        $paramsName = get_method_argNames($this, $this->actionName . 'Action');

        //Get params in the right order
        $routeVars = $this->app->getRoute()->getVars();
        $vars = array();
        if (count($routeVars) > 0) {
            $vars = array_flip($paramsName);
            foreach ($vars as $key => $val) {
                $vars[$key] = array_key_exists($key, $routeVars) ? $routeVars[$key] : null;
            }
            unset($var);
        }

		call_user_func_array(array($this, $this->actionName . 'Action'), $vars);
	}

    /**
     * Gets the current request send to the application
     * 
     * @return Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->app->getComponent('symfony\request');
    }

    /**
     * Gets bundle name.
     * 
     * @return string
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }
    
    /**
     * Sets the bundle name.
     *
     * @param string $bundleName the bundle name
     */
    public function setBundleName($bundleName)
    {
        if (is_string($bundleName) && !empty($bundleName)) {
        	$this->bundleName = $bundleName;
        } else {
        	throw new \InvalidArgumentException('Invalid bundle "'.(string) $bundleName.'"');
        }
    }

    /**
     * Gets the action name.
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }
    
    /**
     * Sets the action name.
     *
     * @param string $actionName the action name
     */
    public function setActionName($actionName)
    {
    	if (is_string($actionName) && !empty($actionName)) {
        	$this->actionName = $actionName;
        } else {
        	throw new \InvalidArgumentException('Invalid action "'.(string) $actionName.'"');
        }
    }

    /**
     * Get the model "$modelName". If $modelName is set to null, returns the model
     * associated with the current controller.
     * 
     * @param string|null $modelName To get a model from another bundle, juste use the bundle name
     */
    public function getModel($bundleName = null)
    {
        if ($bundleName === null) {
            $bundleName = $this->bundleName;
        } else {
            $bundleName = $bundleName . 'Bundle';
        }

        //Create a new instance of the model
        if (!array_key_exists($bundleName, $this->models)) {
            $modelPath = $bundleName . '\\' . $bundleName . 'Model';
            $this->models[$bundleName] = new $modelPath($this->app);            
        }

        return $this->models[$bundleName];
    }
}