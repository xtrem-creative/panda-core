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
        $config = new Configuration();

        if ($connectionName === null) {
            $defaultConnection = ConfigManager::get('database.default');
            $connectionParams = ConfigManager::get('database.list.' . $defaultConnection);
        } else {
            $connectionParams = ConfigManager::get('database.list.' . $connectionName);
        }

        return DriverManager::getConnection($connectionParams, $config);
    }
}