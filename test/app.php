<?php

define('WEB_ROOT', str_replace('/app.php', '', $_SERVER['PHP_SELF']) . '/');
define('APP_DIR', __DIR__ . '/app/');
define('RESOURCES_DIR', __DIR__ . '/resources/');
define('APP_NAMESPACE', 'PandaTest');

require_once '../vendor/autoload.php';

$app = new Panda\Core\Application();
$app->run();