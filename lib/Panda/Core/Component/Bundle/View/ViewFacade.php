<?php

namespace Panda\Core\Component\Bundle\View;

use InvalidArgumentException;
use Panda\Core\Component\Bundle\View\Exception\MissingTemplateEngineException;
use Panda\Core\Component\Bundle\View\Resolver\BladeView;
use Panda\Core\Component\Bundle\View\Resolver\PhpView;
use Panda\Core\Component\Bundle\View\Resolver\TwigView;
use Panda\Core\Component\Bundle\View\Resolver\XslView;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ViewFacade
 *
 * A common interface to manage the view and its contents. This facade is able to handle php, twig, xsl and blade views:
 * the proper view is called given the view file extension (respectively .php, .twig, .xsl and .blade.php).
 *
 * @package Panda\Core\Component\Bundle\View
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */
class ViewFacade implements View
{
    protected $request;
    protected $response;
    protected $vars = array();
    protected $viewPath;

    public function __construct(Request $request, Response $response)
    {
        if (!file_exists(RESOURCES_DIR . 'cache/view')) {
            mkdir(RESOURCES_DIR . 'cache/view');
        }
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Get current request
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get current HTTP code
     * @return int
     */
    public function getHttpCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * Set current HTTP code
     * @param $httpCode
     */
    public function setHttpCode($httpCode)
    {
        $this->response->setStatusCode($httpCode);
    }

    /**
     * Get current Content Type
     * @return array|string
     */
    public function getContentType()
    {
        return $this->response->headers->get('Content-Type');
    }

    /**
     * Set current Content Type
     * @param $contentType
     */
    public function setContentType($contentType)
    {
        $this->response->headers->set('Content-Type', $contentType);
    }

    /**
     * Bind a var to the view
     * @param $name
     * @param $value
     */
    public function setVar($name, $value)
    {
        if (is_string($name) && !empty($name)) {
            if (!array_key_exists($name, $this->vars)) {
                $this->vars[$name] = $value;
            } else {
                throw new RuntimeException('The template var "' . (string) $name . '" has already been defined.');
            }
        } else {
            throw new InvalidArgumentException('Invalid template var "' . (string) $name . '"');
        }
    }

    /**
     * Get a var value given its name
     * @param $name
     * @return mixed
     */
    public function getVar($name)
    {
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        }
        throw new InvalidArgumentException('Unknown template var "' . (string) $name . '"');
    }

    /**
     * Check whether a var exists given its name
     * @param $name
     * @return bool
     */
    public function hasVar($name)
    {
        return array_key_exists($name, $this->vars);
    }

    /**
     * Get currently bound view path
     * @return mixed
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * Bind current view path
     * @param mixed $viewPath
     */
    public function setViewPath($viewPath)
    {
        $this->viewPath = $viewPath;
    }

    /**
     * Render currently bound view and set result in response
     * @param string|null $templateName
     * @param array|null $vars
     */
    public function render($templateName = null, $vars = null)
    {
        if ($templateName === null && ($this->getHttpCode() < 200 || $this->getHttpCode() > 226)) {
            if (is_file(RESOURCES_DIR . 'template/error.php')) {
                $templateName = RESOURCES_DIR . 'template/error.php';
            } else if (is_file(RESOURCES_DIR . 'template/error.twig')) {
                $templateName = RESOURCES_DIR . 'template/error.twig';
            } else if (is_file(RESOURCES_DIR . 'template/error.blade.php')) {
                $templateName = RESOURCES_DIR . 'template/error.blade.php';
            } else {
                //Render error default page
                $templateName = __DIR__ . '/Resource/default_error.php';
            }

            if (array_key_exists('message', $this->vars)) {
                $this->vars = array(
                    'errorCode' => $this->getHttpCode(),
                    'message' => $this->vars['message']
                );
            } else {
                $this->vars = array(
                    'errorCode' => $this->getHttpCode()
                );
            }
        }
        if ($templateName === null) {
            if ($this->viewPath !== null) {
                $templateName = $this->viewPath;
            } else {
                return;
            }
        }
        if (is_file($templateName)) {
            $bundleViewsDir = substr($templateName, 0, strrpos($templateName, '/'));
            if (str_ends_with($templateName, '.twig')) {
                /**
                 * Use Twig template engine
                 * @link http://twig.sensiolabs.org/
                 */
                if (class_exists('Twig_Loader_Filesystem') && class_exists('Twig_Environment') && class_exists
                    ('Twig_SimpleFilter')
                ) {
                    $tplEngine = new TwigView($this, RESOURCES_DIR . 'template/', $bundleViewsDir,
                        RESOURCES_DIR . 'cache/view/');
                } else {
                    throw new MissingTemplateEngineException('The Twig template engine is missing :/ Please add the
                    required dependency : "twig/twig"');
                }
            } else if (str_ends_with($templateName, '.blade.php')) {
                /**
                 * Use Blade template engine
                 * @link http://laravel.com/docs/4.2/templates
                 */
                if (class_exists('Philo\Blade\Blade')) {
                    $tplEngine = new BladeView($this, RESOURCES_DIR . 'template/', $bundleViewsDir,
                        RESOURCES_DIR . 'cache/view/');
                } else {
                    throw new MissingTemplateEngineException('The Blade template engine is missing :/ Please add the
                    required dependency : "philo/laravel-blade"');
                }
            } else if (str_ends_with($templateName, '.php')) {
                /**
                 * Use raw php for templates
                 */
                $tplEngine = new PhpView($this);
            }  else if (str_ends_with($templateName, '.xsl')) {
                /**
                 * Use XSLT processor for templates
                 */
                $tplEngine = new XslView($this);
            } else {
                throw new InvalidArgumentException('No template engine found for "' . (string)$templateName . '"');
            }

            $this->response->setContent($tplEngine->render($templateName, $this->vars));
        } else {
            throw new InvalidArgumentException('"' . (string)$templateName . '" template doesn\'t exists');
        }
    }
} 