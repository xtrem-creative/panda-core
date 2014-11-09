<?php

namespace Panda\Core\Component\Bundle\Dao;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Panda\Core\Component\Config\ConfigManager;

abstract class AbstractBasicDao
{
    protected static $connections = array();

    public function getConnection($connectionName = null)
    {
        if ($connectionName === null) {
            $connectionName = ConfigManager::get('database.default');
        }

        if (!array_key_exists(self::$connections, $connectionName)) {
            $config = new Configuration();

            $connectionParams = ConfigManager::get('database.list.' . $connectionName);

            self::$connections[$connectionName] = DriverManager::getConnection($connectionParams, $config);
        }

        return self::$connections[$connectionName];
    }
}