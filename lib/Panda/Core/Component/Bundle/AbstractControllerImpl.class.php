<?php

namespace Panda\Core\Component\Bundle;


class AbstractControllerImpl implements Controller
{
    public function exec()
    {
        $c = new ReflectionClass($this);
        $methodsList = $c->getMethods();
        var_dump($methodsList);
    }

    public function getDao($daoName)
    {
        // TODO: Implement getDao() method.
    }
}