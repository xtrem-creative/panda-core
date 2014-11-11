<?php

namespace Panda\Core\Event;


interface Observer
{
    public function update(Observable $subject);
} 