<?php

/**
 * Panda component
 * 
 * Panda is divided in two parts: the kernel and the components.
 * The kernel is the Application, and its main purpose is to give
 * an access point for the user to the framework features.
 * The components role is to add a feature to the kernel (manage the
 * configuration, the datasources, etc.)
 * 
 * This class is the base of each component.
 * 
 * @package Panda
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */

namespace Panda\component;

use Panda\Application;

abstract class AbstractComponent
{
	protected $app;

	/**
	 * Construct a component from the application
	 * 
	 * @param Panda\component\Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Get current application
	 * 
	 * @return AbstractApplication
	 */
	public function getApp()
	{
		return $this->app;
	}
}