<?php

namespace Panda\component\router;

use Panda\component\AbstractComponent;
use Panda\Application;

class Router extends AbstractComponent
{

	const NO_ROUTE_FOUND = 1;

	private $_routes = array();

	public function __construct(Application $app)
	{
		parent::__construct($app);
		$this->_loadRoutes();
	}

	/**
	 * Load routes from routes config files
	 * 
	 * @throws RuntimeException
	 */
	private function _loadRoutes()
	{
		$bundlesConfigs = glob(APP_DIR . '*Bundle/res/config/routes.php');

		foreach ($bundlesConfigs as $bundleConfig) {
			$configContent = require_once $bundleConfig;
			$bundleName = str_replace('/res/config/routes.php', '', str_replace(APP_DIR, '', $bundleConfig));
			$currentFilter = null;
			$i = 0;
			foreach ($configContent as $route => $config) {
				if ($route === 'filter') {
					if ($i === 0) {
						$currentFilter = $config;
					} else {
						throw new \RuntimeException('Unable to use the global filter in "' . $bundleName . '": the filter must be the first row.');
					}
				} else {
					if (array_key_exists($route, $this->_routes)) {
						throw new \RuntimeException('Duplicate route "'.$route.'" in "'.$bundleName.'"');
						
					} else {
						$filter = $currentFilter !== null ? $currentFilter : (array_key_exists('filter', $config) ? $config['filter'] : null);
						$fallback = array_key_exists('fallback', $config) ? $config['fallback'] : null;
						$vars = array_key_exists('vars', $config) ? $config['vars'] : null;
						$this->_routes[$route] = new Route($route, $bundleName, $config['action'], $filter, $fallback, $vars);
					}
				}
				++$i;
			}
		}
	}

	/**
	 * Tries to get a matching route for the given url
	 * 
	 * @param string $url
	 * @return Route
	 * @throws RuntimeException
	 */
	public function getMatchingRoute($url) {
		foreach ($this->_routes as $route) {
			if ($route->match($url)) {
				return $route;
			}
		}
		throw new \RuntimeException('No route found for the asked URL', self::NO_ROUTE_FOUND);
	}
}