<?php

namespace Panda\Core\Component\Config;

class ConfigManager
{
    private static $instance;
    private $config = array();
    private $configHasChanged = false;

    private function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Set an entry in the config
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        self::getInstance()->setVar($key, $value);
    }

    /**
     * Get an entry from the config
     * @param $key
     * @return array|null
     */
    public static function get($key)
    {
        return self::getInstance()->getVar($key);
    }

    /**
     * Remove an entry from the config
     * @param $key
     */
    public static function remove($key)
    {
        self::getInstance()->removeVar($key);
    }

    /**
     * Check whether a config entry identified by the given key exists
     * @param $key
     * @return bool
     */
    public static function exists($key)
    {
        return self::getInstance()->varExists($key);
    }

    /**
     * Get the list of all known config entries
     * @return array
     */
    public static function getList()
    {
        return self::getInstance()->getAllConfig();
    }

    /**
     * Save the current config
     */
    public static function saveAll()
    {
        self::getInstance()->saveConfig();
    }

    /***
     * Check whether the config has changed
     * @return bool
     */
    public static function configHasChanged()
    {
        return self::getInstance()->getConfigHasChanged();
    }

    private static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ConfigManager();
        }
        return self::$instance;
    }

    public function setVar($key, $value)
    {
        $this->setKeyRecursive($key, $value, $this->config);
        $this->configHasChanged = true;
    }

    private function setKeyRecursive($key, $value, array &$array)
    {
        $pos = strpos($key, '.');
        if ($pos !== false) {
            $this->setKeyRecursive(substr($key, $pos + 1), $value, $array[substr($key, 0, $pos)]);
        } else {
            if (isset($array)) {
                $array[$key] = $value;
            } else {
                throw new \InvalidArgumentException('Unknown key "'.(string) $key.'"');
            }
        }
    }

    public function getVar($key)
    {
        return $this->getKeyRecursive($this->config, $key);
    }

    private function getKeyRecursive(array $array, $key, $default = null)
    {
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

    public function removeVar($key)
    {
        //TODO!
    }

    public function varExists($key)
    {
        $current = $this->config;
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

    private function loadConfig()
    {
        if (is_file(RESOURCES_DIR . 'config/config.json')) {
            $this->config = json_decode(file_get_contents(RESOURCES_DIR . 'config/config.json'), true);
        } else {
            throw new \RuntimeException('Unable to load the config file');
        }
    }

    private function saveConfig()
    {
        file_put_contents(RESOURCES_DIR . 'config/config.json', json_encode($this->getAllConfig()));
    }

    private function getAllConfig()
    {
        return $this->config;
    }

    private function getConfigHasChanged()
    {
        return $this->configHasChanged;
    }
} 