<?php

/**
 * Panda view
 * 
 * This is the class which manage the view part of the MVC pattern.
 * It wraps the tools to generate a brand new response for the client.
 * 
 * @package Panda
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */

namespace Panda\component\bundle;

use Panda\component\AbstractComponent;
use Panda\Application;
use Symfony\Component\HttpFoundation\Response;

class View extends AbstractComponent
{
	private $_httpCode = 200;
	private $_contentType = 'text/html';
	private $_vars = array();
	private $_bundleName;
	private $_components = array();

	/**
     * Construct a view
     * 
     * @param Panda\Application $app
     * @param int $httpCode The HTTP code, in function of the situation (200 stands for OK)
     */
	public function __construct(Application $app, $httpCode = 200)
	{
		parent::__construct($app);
		$this->setHttpCode($httpCode);
	}

	/**
	 * Add a variable to the view
	 * 
	 * @param string $varName
	 * @param mixed $varValue
	 * @throws RuntimeException If the $varName is already used
	 * @throws InvalidArgumentException If the $varName is invalid
	 */
	public function addVar($varName, $varValue)
	{
		if (is_string($varName) && !empty($varName)) {
			if (!array_key_exists($varName, $this->_vars)) {
				$this->_vars[$varName] = $varValue;
			} else {
				throw new \RuntimeException('The template var "' . (string) $varName . '" has already been defined.');	
			}
		} else {
			throw new \InvalidArgumentException('Invalid template var "' . (string) $varName . '"');
		}
	}

	public function getVar($varName)
	{
		if (array_key_exists($varName, $this->_vars)) {
			return $this->_vars[$varName];
		}
		throw new \InvalidArgumentException('Unknown template var "' . (string) $varName . '"');
	}

	/**
	 * Sets the HTTP code for the current view
	 * 
	 * @param int $httpCode
	 */
	public function setHttpCode($httpCode)
	{
		$acceptedHTTPCodes = array(
			100,//Continue
			101,//Switching Protocols
			102,//Processing
			118,//Connection timed out
			200,//OK
			201,//Created
			202,//Accepted
			203,//Non-Authoritative Information
			204,//No Content
			205,//Reset Content
			206,//Partial Content
			207,//Multi-Status
			210,//Content Different
			226,//IM used
			300,//Multiple Choices
			301,//Moved Permanently
			302,//Found
			303,//See Other
			304,//Not Modified
			305,//Use Proxy
			307,//Temporary Redirect
			308,//Permanent Redirect
			310,//Too many Redirects
			400,//Bad Request
			401,//Unauthorized
			402,//Payment Required
			403,//Forbidden
			404,//Not Found
			405,//Method Not Allowed
			406,//Not Acceptable
			407,//Proxy Authentication Required
			408,//Request Time-out
			409,//Conflict
			410,//Gone
			411,//Length Required
			412,//Precondition Failed
			413,//Request Entity Too Large
			414,//Request-URI Too Long
			415,//Unsupported Media Type
			416,//Requested range unsatisfiable
			417,//Expectation failed
			418,//I’m a teapot
			426,//Upgrade Required
			428,//Precondition Required
			429,//Too Many Requests
			431,//Request Header Fields Too Large
			500,//Internal Server Error
			501,//Not Implemented
			502,//Bad Gateway ou Proxy Error
			503,//Service Unavailable
			504,//Gateway Time-out
			505//HTTP Version not supported
		);

		if (is_int($httpCode) && !empty($httpCode) && in_array($httpCode, $acceptedHTTPCodes)) {
			$this->_httpCode = $httpCode;
		} else {
			throw new \InvalidArgumentException('Invalid HTTP code "' . $httpCode . '"');
		}
	}

	/**
	 * Gets the current HTTP code
	 * 
	 * @return int
	 */
	public function getHttpCode() {
		return $this->_httpCode;
	}

	public function setContentType($contentType) {
		if (is_string($contentType) && !empty($contentType)) {
			$this->_contentType = $contentType;
		}
	}

	/**
	 * Sets the bundle name
	 * 
	 * @param string The bundle name
	 * @throws InvalidArgumentException
	 */
	public function setBundleName($bundleName)
	{
		if (is_string($bundleName) && !empty($bundleName)) {
			$this->_bundleName = $bundleName;
		} else {
			throw new \InvalidArgumentException('Invalid bundle name "' . (string) $bundleName . '"');
		}
	}

	/**
	 * Gets the bundle name
	 * 
	 * @return string
	 */
	public function getBundleName()
	{
		return $this->_bundleName;
	}

	/**
	 * Render the given template
	 * 
	 * @param string $template
	 * @throws InvalidArgumentException
	 */
	public function render($template)
	{
		if ($this->_httpCode >= 200 && $this->_httpCode <= 226) {
			//Normal stuff
			$twigLoader = new \Twig_Loader_Filesystem(array(
				SHARE_DIR . 'template',
				APP_DIR . $this->_bundleName . '/view'
				)
			);
			$twigLoader->addPath(SHARE_DIR . 'template', 'layouts');
		} else {
			//Something goes wrong
			if (is_file(SHARE_DIR . 'template/' . $this->_httpCode . '.twig')) {
				$twigLoader = new \Twig_Loader_Filesystem(array(
						SHARE_DIR . 'template'
					)
				);
			} else {
				$twigLoader = new \Twig_Loader_Filesystem(array(
						VENDOR_DIR . 'panda/template'
					)
				);
			}
		}

		$devMode = $this->app->getComponent('config\Config')->get('panda.mode') === 'dev' ? true : false;

		$twig = new \Twig_Environment($twigLoader, array(
				'cache' => CACHE_DIR . 'template',
				'debug' => $devMode,
				'auto_reload' => $devMode
			));

		//Global vars definition
		$twig->addGlobal('webroot', WEB_ROOT);
		$twig->addGlobal('currentUrl', $this->app->getComponent('symfony\request')->getPathInfo());
		$twig->addGlobal('view', $this);

		try {
			$twig->addGlobal('user', $this->app->getService('user'));
		} catch (\RuntimeException $e) {
			
		}

		//Custom filters
		$twig->addFilter(new \Twig_SimpleFilter('url_transform', 'url_transform'));
		$twig->addFilter(new \Twig_SimpleFilter('repeat', 'str_repeat'));
		$twig->addFilter(new \Twig_SimpleFilter('truncate', 'truncate'));
		$twig->addFilter(new \Twig_SimpleFilter('strip_tags', 'strip_tags'));

		if ($this->_httpCode >= 200 && $this->_httpCode <= 226) {
			if (is_string($template) && !empty($template) && is_file(APP_DIR . $this->_bundleName . '/view/' . $template)) {
				$this->app->successExit($twig->render($template, $this->_vars), $this->_httpCode, $this->_contentType);
			} else {
				throw new \InvalidArgumentException('Unknown template "'.(string) $template.'"');
			}
		} else {
			if (is_file(SHARE_DIR . 'template/' . $this->_httpCode . '.twig') || is_file(VENDOR_DIR . 'panda/template/'.$this->_httpCode.'.twig')) {
				return $twig->render($this->_httpCode . '.twig', $this->_vars);
			} else {
				return $twig->render('error.twig', $this->_vars);
			}
		}
	}

	public function getComponent($componentName)
	{
		//Create a new instance of the component
        if (!array_key_exists($componentName, $this->_components)) {
        	if (is_file(SHARE_DIR . 'plugin/view/' . $componentName . '.class.php')) {
        		$componentPath = 'share\plugin\view\\' . $componentName;
        	} else {
            	$componentPath = 'Panda\component\bundle\plugin\view\\' . $componentName;
            }
            $this->_components[$componentName] = new $componentPath($this);            
        }

        return $this->_components[$componentName];
	}
}