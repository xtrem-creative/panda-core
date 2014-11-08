<?php

namespace PandaTest\ExampleBundle;

use Panda\Core\Component\Bundle\AbstractControllerImpl;

class ExampleBundleController extends AbstractControllerImpl
{
    /**
     * @RequestMapping(value="/", method="GET")
     * @Secured([ "ROLE_MEMBER" ])
     */
    public function testGetAction()
    {

    }

    /**
     * @RequestMapping(value="/test", method="POST")
     */
    public function testPostAction()
    {

    }

    /**
     * @RequestMapping(value="/([a-z])-([a-z])-([a-z])-([a-z])", method="GET")
     */
    public function testGetParamAction($a, $b, $d, $c)
    {

    }
} 