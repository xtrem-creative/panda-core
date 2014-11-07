<?php
namespace Panda\component\error;

use Panda\Application;
use Panda\component\AbstractComponent;

class ErrorHandler extends AbstractComponent {

	private $_mode = 'dev';
	private $_logFile;

	public function __construct(Application $app)
	{
		parent::__construct($app);
		$this->setMode($app->getComponent('config\Config')->get('panda.mode'));

		ini_set('display_errors', 0);

		if ($this->_mode === 'prod') {
			ini_set('log_errors', 1);
			$this->_logFile = SHARE_DIR . 'log/log-'.date('Y-m-d').'.txt';
		} else if ($this->_mode !== 'dev') {
			$app->getComponent('config\Config')->set('panda.mode', 'dev');
		}

		register_shutdown_function(array($this, 'handleFatal'));
		set_error_handler(array($this, 'handleError'));
		set_exception_handler(array($this, 'handleException'));
		error_reporting(E_ALL);
	}

	public function setMode($mode)
	{
		if ($mode === 'dev' || $mode === 'prod') {
			$this->_mode = $mode;
		} else {
			throw new \InvalidArgumentException('Invalid site mode "'.$mode.'"');
		}
	}

	public function handleError($num, $str, $file, $line, $context = null)
	{
		$this->handleException(new \ErrorException($str, 0, $num, $file, $line));
	}

	public function handleException(\Exception $e)
	{
		if ($this->_mode === 'dev') {
			print "<div style='text-align: center;'>";
			print "<h2 style='color: rgb(190, 50, 50);'>Exception Occured:</h2>";
			print "<table style='margin: auto;'>";
			print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Type</th><td>" . get_class( $e ) . "</td></tr>";
			print "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td>{$e->getMessage()}</td></tr>";
			print "<tr style='background-color:rgb(230,230,230);'><th>File</th><td>{$e->getFile()}</td></tr>";
			print "<tr style='background-color:rgb(240,240,240);'><th>Line</th><td>{$e->getLine()}</td></tr>";
			print "</table></div>";
		} else {
			$message = '['.date('H:i:s').']' . " Type: " . get_class( $e ) . "; Message: {$e->getMessage()}; File: {$e->getFile()}; Line: {$e->getLine()};";
			chmod(SHARE_DIR . 'log', 0755);
			file_put_contents($this->_logFile, $message . PHP_EOL, FILE_APPEND);
			$this->app->emergencyExit(500);
		}
	}

	public function handleFatal()
	{
    	$error = error_get_last();
    	if ($error !== null) {
        	$this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
	}
}