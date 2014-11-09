<?php

namespace PandaTest\ExampleBundle;

use Panda\Core\Component\Bundle\Dao\AbstractBasicDao;

class TestDao extends AbstractBasicDao
{
    public function createTestDb()
    {
        $sql = 'CREATE TABLE test(
            id INTEGER PRIMARY KEY,
            name VARCHAR NOT NULL
        )';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
    }

    public function selectTestResults()
    {
        $sql = 'SELECT * FROM test';
        return $this->getConnection()->query($sql);
    }
}