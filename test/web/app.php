<?php

define('WEB_ROOT', str_replace('/app.php', '', $_SERVER['PHP_SELF']) . '/');
define('ROOT', dirname(__DIR__) . '/');
define('APP_NAMESPACE', 'PandaTest');

require_once '../../vendor/autoload.php';

$app = new Panda\Core\Application();
$app->run();