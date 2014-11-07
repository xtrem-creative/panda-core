<?php

namespace Panda\component\user\component;

use Panda\component\user\User;

abstract class AbstractUserComponent
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}