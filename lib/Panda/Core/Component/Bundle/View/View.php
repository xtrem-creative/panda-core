<?php

namespace Panda\Core\Component\Bundle\View;


interface View
{
    public function render($templateName, $vars = null);
}