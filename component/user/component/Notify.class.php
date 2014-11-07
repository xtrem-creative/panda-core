<?php

namespace Panda\component\user\component;

use Panda\component\user\User;

class Notify extends AbstractUserComponent
{
    private $_notifications = array();

    const INFO = 1;
    const WARNING = 2;
    const ERROR = 3;
    const SUCCESS = 4;

    public function __construct(User $user)
    {
        if ($user->getSession()->has('notify')) {
            $this->hydrate($user->getSession()->get('notify'));
            $user->getSession()->remove('notify');
        }
        parent::__construct($user);
    }

    public function hydrate($sessionData)
    {
        $this->_notifications = $sessionData;
    }

    public function hasNotifications()
    {
        return !empty($this->_notifications);
    }

    public function getNotifications()
    {
        return $this->_notifications;
    }

    public function send($content, $type = self::INFO)
    {
        $this->_notifications[] = array('content' => $content, 'type' => $type);
        $this->user->getSession()->set('notify', $this->_notifications);
    }
}