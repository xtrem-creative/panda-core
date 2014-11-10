<?php

namespace Panda\Core\Component\Router\Provider;

interface RoutesProvider
{
    public function reloadRoutes($reloadCache = false);

    public function getRoutesList();
} 