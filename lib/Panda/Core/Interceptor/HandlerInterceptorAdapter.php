<?php

namespace Panda\Core\Interceptor;


use Panda\Core\Component\Bundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HandlerInterceptorAdapter implements HandlerInterceptor
{
    public function preHandle(Request $request)
    {
        return true;
    }

    public function postHandle(Request $request, Response $response, View $view)
    {
        return true;
    }

    public function afterCompletition(Request $request, Response $response)
    {
        return true;
    }
}