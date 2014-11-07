<?php

namespace Panda\component\config;

use Panda\component\AbstractComponent;
use Panda\Application;

class Config extends AbstractComponent {
	private $_config = array();

	public function __construct(Application $app)
	{
		parent::__construct($app);
		$this->_loadConfigFile();
	}

	/**
	 * Loads configuration from the config.json file
	 * 
	 * @throws RuntimeException
	 */
	private function _loadConfigFile()
	{
		if (is_file(SHARE_DIR . 'config/config.json')) {
			$this->_config = json_decode(file_get_contents(SHARE_DIR . 'config/config.json'), true);
		} else {
			throw new \RuntimeException('Unable to load the config file');	
		}
	}

	/**
	 * Rebuilds the configuration file from the $_config attribute
	 */
	private function _rebuildConfigFile()
	{
		file_put_contents(SHARE_DIR . 'config/config.json', json_encode($this->_config));
	}

	/**
	 * Checks whether the given key exists in the configuration
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function exists($key) {
		$current = $this->_config;
		$p = strtok($key, '.');

		while ($p !== false) {
			if (!isset($current[$p])) {
				return false;
			}
			$current = $current[$p];
			$p = strtok('.');
		}
		return true;
	}

	/**
	 * Gets the config value matching with $key
	 * 
	 * @param string $key
	 * @return mixed|null
	 */
	public function get($key) {
		return $this->_getKeyRecursive($this->_config, $key);
	}

	/**
	 * Gets the array value matching with the index $key, using the dot notation.
	 * 
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	private function _getKeyRecursive(array $array, $key, $default = null) {
		$current = $array;
		$p = strtok($key, '.');

		while ($p !== false) {
			if (!array_key_exists($p, $current)) {
				return $default;
			}
			$current = $current[$p];
			$p = strtok('.');
		}
		return $current;
	}

	public function set($key, $data)
	{
		$this->_setKeyRecursive($key, $value, $this->_config);			
		$this->_rebuildConfigFile();
	}

	/**
	 * Finds $array value matching with $key, and set its value to $value
	 * 
	 * @param $key string
	 * @param $value mixed
	 * @param $array &array
	 * @throws InvalidArgumentException
	 */
	private function _setKeyRecursive($key, $value, array &$array) {
		$pos = strpos($key, '.');
		if ($pos !== false) {
			$this->_setKeyRecursive(substr($key, $pos + 1), $value, $array[substr($key, 0, $pos)]);
		} else {
			if (isset($array)) {
				$array[$key] = $value;
			} else {
				throw new \InvalidArgumentException('Unknown key "'.(string) $key.'"');
			}
		}
	}
}