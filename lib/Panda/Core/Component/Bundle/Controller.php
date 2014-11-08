<?php

namespace Panda\Core\Component\Bundle;


interface Controller
{
    public function exec();

    public function getDao($daoName);
} 