<?php

namespace Panda\component\router;

class Route
{
	private $_url;
	private $_bundleName;
	private $_actionName;
	private $_filters;
	private $_fallback;
	private $_vars = array();
   
	public function __construct($url, $bundleName, $actionName, $filters, $fallback, array $vars = null)
	{
		$this->setUrl($url);
		$this->setBundleName($bundleName);
		$this->setActionName($actionName);
		$this->setFilters($filters);
		$this->setFallback($fallback);
		$this->setVars($vars);
	}
   
	/**
	* Check if the route matches the pattern $url
	* 
	* @param string $url The pattern to match with
	* @return bool
	*/
	public function match($url)
	{
		$matches = array();
		if (preg_match('`^' . $this->_url . '$`', $url, $matches)) {
			if ($this->_vars !== array()) {
				unset($matches[0]);
				$matches = array_values($matches);
				$this->_vars = array_combine($this->_vars, $matches);
			}
			return true;
		} else {
			return false;
		}
	}
   
	/**
	* Set the pattern for the current route
	* 
	* @param string $url
	*/
	public function setUrl($url)
	{
		if (is_string($url) || !empty($url)) {
			$this->_url = $url;
		}
	}
   
   /**
    * Set the bundle name for the current route
    * 
    * @param string $bundleName
    */
	public function setBundleName($bundleName)
	{
		if (is_string($bundleName) || !empty($bundleName)) {
			$this->_bundleName = $bundleName;
		}
	}
   
   /**
    * Set the action name for the current route
    * 
    * @param string $actionName
    */
	public function setActionName($actionName)
	{
		if (is_string($actionName) || !empty($actionName)) {
			$this->_actionName = $actionName;
		}
	}
   
   	/**
   	 * Set the vars names for the current route
   	 * 
   	 * @param array $vars
   	 */
	public function setVars(array $vars = null)
	{
		if (is_array($vars)) {
			foreach ($vars as $var) {
				if (!is_string($var) || empty($var)) {
					return;
				}
			}
			$this->_vars = $vars;
		}
	}

	public function setFilters($filters)
	{
		$this->_filters = $filters;
	}

	public function setFallback($fallback)
	{
		$this->_fallback = $fallback;
	}

	public function getUrl()
	{
		return $this->_url;
	}

	public function getBundleName()
	{
		return $this->_bundleName;
	}

	public function getActionName()
	{
		return $this->_actionName;
	}

	public function getVars()
	{
		return $this->_vars;
	}

	public function getFilters()
	{
		return $this->_filters;
	}

	public function getFallback()
	{
		return $this->_fallback;
	}
}