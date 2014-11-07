<?php

/**
 * Panda framework autoloader 
 * 
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */

require_once 'inc/function/dev.php';
require_once 'inc/function/string.php';
require_once 'inc/function/hash.php';
require_once 'inc/function/dir.php';
require_once 'inc/const/dir.php';
require_once VENDOR_DIR . 'autoload.php';

/**
 * Autoload a class using its name
 * 
 * @param string $className
 * @throws RuntimeException
 */
function autoloader($className) {
	$namespace = substr($className, 0, strpos($className, '\\'));
	$classPath = str_replace('\\', '/', substr($className, strpos($className, '\\') + 1));
	$root = ROOT;
	
	if ($namespace === 'Panda') {
		$root .= 'vendor/panda/';
	} else if (str_ends_with($namespace, 'Bundle')) {
		$root = APP_DIR . $namespace . '/';
	} else {
		$root .= $namespace . '/';
	}

	if (is_file($root . $classPath . '.class.php')) {
		require_once $root . $classPath . '.class.php';
	} else if (is_file($root . $classPath . '.php')) {
		require_once $root . $classPath . '.php';
	} else {
		throw new RuntimeException('Unknown class "'.$className.'"');	
	}
}

spl_autoload_register('autoloader');