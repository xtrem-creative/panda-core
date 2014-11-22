<?php

namespace PandaTest\ExampleBundle;

use Panda\Core\Component\Bundle\AbstractController;

class ExampleBundleController extends AbstractController
{
    /**
     * @RequestMapping(value="/")
     */
    public function testGetTwigAction($name = 'panda')
    {
        $this->getDao('Test')->createTestDb();
        $this->view->setVar('queryResults', $this->getDao('Test')->selectTestResults());
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
     * @RequestMapping(value="/php", method="GET")
     */
    public function testGetPhpAction($name = 'panda')
    {
        $this->view->setVar('name', htmlspecialchars($name));
        return "test.php";
    }

    /**
     * @RequestMapping(value="/xslt", method="GET")
     */
    public function testGetXsltAction($name = 'panda')
    {
        $this->view->setVar('name', htmlspecialchars($name));
        return "test.xsl";
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