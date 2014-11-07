<?php

namespace Panda\component\bundle\plugin\view;

abstract class AbstractViewPlugin implements ViewPluginInterface
{

    protected $view;

    public function __construct(\Panda\component\bundle\View $view)
    {
        $this->view = $view;
    }
}