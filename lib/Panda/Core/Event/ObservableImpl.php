<?php

namespace Panda\Core\Event;


use SplObserver;

class ObservableImpl implements \SplSubject
{
    private $observers = array();

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Attach an SplObserver
     * @link http://php.net/manual/en/splsubject.attach.php
     * @param SplObserver $observer <p>
     * The <b>SplObserver</b> to attach.
     * </p>
     * @throws \RuntimeException
     * @return void
     */
    public function attach(SplObserver $observer)
    {
        if (in_array($observer, $this->observers)) {
            throw new \RuntimeException('Already bind observer.');
        }
        $this->observers[] = $observer;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Detach an observer
     * @link http://php.net/manual/en/splsubject.detach.php
     * @param SplObserver $observer <p>
     * The <b>SplObserver</b> to detach.
     * </p>
     * @throws \RuntimeException
     * @return void
     */
    public function detach(SplObserver $observer)
    {
        if (!in_array($observer, $this->observers)) {
            throw new \RuntimeException('Observer not found.');
        }
        unset($this->observers[array_search($observer, $this->observers)]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Notify an observer
     * @link http://php.net/manual/en/splsubject.notify.php
     * @return void
     */
    public function notify()
    {
        if (!empty($this->observers)) {
            foreach ($this->observers as $obs) {
                $obs->notify();
            }
        }
    }
}