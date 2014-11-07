<?php

/**
 * Panda view plugin interface
 * 
 * An interface to design a plugin for the view.
 * 
 * @package Panda
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */

namespace Panda\component\bundle\plugin\view;

interface ViewPluginInterface
{
    public function render();
}