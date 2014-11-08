<?php

define('WEB_ROOT', str_replace('/app.php', '', $_SERVER['PHP_SELF']) . '/');
define('APP_DIR', __DIR__ . '/app/');
define('APP_NAMESPACE', 'PandaTest');

require_once '../vendor/autoload.php';
require_once '../lib/Panda/Core/Autoloader.class.php';

//--------------------------

require_once 'app/ExampleBundle/ExampleBundleController.class.php';

//--------------------------

$app = new Panda\Core\Application();
$app->run();