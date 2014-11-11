<?php

namespace Panda\Core\Event;


class ObservableImpl implements Observable
{
    private $observers = array();

    public function bindObserver(Observer $observer)
    {
        if (in_array($observer, $this->observers)) {
            throw new \RuntimeException('Already bind observer.');
        }
        $this->observers[] = $observer;
    }

    public function unbindObserver(Observer $observer)
    {
        if (!in_array($observer, $this->observers)) {
            throw new \RuntimeException('Observer not found.');
        }
        unset($this->observers[array_search($observer, $this->observers)]);
    }

    public function notifyObservers()
    {
        if (!empty($this->observers)) {
            foreach ($this->observers as $obs) {
                $obs->notify();
            }
        }
    }
}