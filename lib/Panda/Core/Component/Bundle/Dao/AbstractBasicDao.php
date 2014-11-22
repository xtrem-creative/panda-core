<?php

namespace Panda\Core\Component\Bundle\Dao;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Panda\Core\Component\Config\ConfigManager;

/**
 * Class AbstractBasicDao
 * A base DAO class to interact with Doctrine Dbal
 * @package Panda\Core\Component\Bundle\Dao
 */
abstract class AbstractBasicDao
{
    protected static $connections = array();

    /**
     * Get a reference to the DBAL connection with the given name.
     * If the connection name is not provided, this method will try to use the
     * default connection as mentionned in the config file.
     *
     * @param string $connectionName
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection($connectionName = null)
    {
        if ($connectionName === null) {
            $connectionName = ConfigManager::get('datasources.default');
        }

        if (!array_key_exists($connectionName, self::$connections)) {
            $config = new Configuration();

            $connectionParams = ConfigManager::get('datasources.list.' . $connectionName);

            self::$connections[$connectionName] = DriverManager::getConnection($connectionParams, $config);
        }

        return self::$connections[$connectionName];
    }
}