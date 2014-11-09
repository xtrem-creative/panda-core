<?php

namespace PandaTest\ExampleBundle;

use Panda\Core\Component\Bundle\AbstractController;

class ExampleBundleController extends AbstractController
{
    /**
     * @RequestMapping(value="/")
     * @Secured([ "ROLE_MEMBER" ])
     */
    public function testGetTwigAction($name = 'panda')
    {
        $this->view->setVar('name', htmlspecialchars($name));
        return "home.twig";
    }

    /**
     * @RequestMapping(value="/blade", method="GET")
     */
    public function testGetBladeAction($name = 'panda')
    {
        $this->view->setVar('name', htmlspecialchars($name));
        return "test.blade.php";
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
    public function testGetParamAction($a, $b, $c, $d, $e = null)
    {
        var_dump($a);
        var_dump($b);
        var_dump($c);
        var_dump($d);
        var_dump($e);
    }
} 