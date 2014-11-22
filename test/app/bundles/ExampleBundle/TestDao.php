<?php

namespace PandaTest\ExampleBundle;

use Panda\Core\Component\Bundle\Dao\AbstractBasicDao;

class TestDao extends AbstractBasicDao
{
    public function createTestDb()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS test(
            id INTEGER PRIMARY KEY,
            name VARCHAR NOT NULL
        );
        INSERT INTO test VALUES(1,"test");
        ';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
    }

    public function selectTestResults()
    {
        $sql = 'SELECT * FROM test';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}