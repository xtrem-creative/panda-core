<?php

namespace Panda\Core\Interceptor;


use Panda\Core\Component\Bundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface HandlerInterceptor
{
    public function preHandle(Request $request);

    public function postHandle(Request $request, Response $response, View $view);

    public function afterCompletition(Request $request, Response $response);
} 