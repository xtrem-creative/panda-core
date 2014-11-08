<?php

define('WEB_ROOT', str_replace('/index.php', '', $_SERVER['PHP_SELF']) . '/');

require_once '../vendor/autoload.php';
require_once '../lib/Panda/Core/Autoloader.class.php';

//--------------------------

require_once 'ExampleController.class.php';

//--------------------------

$app = new Panda\Core\Application();
$app->run();