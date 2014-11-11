<?php

namespace Panda\Core\Event;


interface Observable
{
    public function bindObserver(Observer $observer);

    public function unbindObserver(Observer $observer);

    public function notifyObservers();
} 