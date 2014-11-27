<?php

namespace Panda\Core\Component\Bundle\View\Resolver;

use Panda\Core\Component\Bundle\View\View;
use Panda\Core\Component\Bundle\View\ViewFacade;

abstract class AbstractViewResolver implements View
{
    protected $viewFacade;

    public function __construct(ViewFacade $viewFacade)
    {
        $this->viewFacade = $viewFacade;
    }
}