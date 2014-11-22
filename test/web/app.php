<?php

define('WEB_ROOT', str_replace('/app.php', '', $_SERVER['PHP_SELF']) . '/');
define('ROOT', dirname(__DIR__) . '/');
define('APP_NAMESPACE', 'PandaTest');
define('VENDORS_DIR', ROOT . '../vendor/');

require_once '../../vendor/autoload.php';

$app = new Panda\Core\Application('dev');
$app->run();