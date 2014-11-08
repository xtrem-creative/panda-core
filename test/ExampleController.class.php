<?php

class ExampleController
{
    /**
     * @RequestMapping(value="/", method=HttpRequest::METH_GET)
     * @Secured([ "ROLE_MEMBER" ])
     */
    public function testGetAction()
    {

    }

    /**
     * @RequestMapping(value="/", method=HttpRequest::METH_POST)
     */
    public function testPostAction()
    {

    }
} 