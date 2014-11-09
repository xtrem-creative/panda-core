<?php

namespace Panda\Core\Component\Bundle\View;


use InvalidArgumentException;
use Panda\Core\Component\Bundle\View\Exception\MissingTemplateEngineException;
use RuntimeException;

class ViewFacade implements View
{
    protected $httpCode = 200;
    protected $contentType = 'text/html';
    protected $vars = array();
    protected $renderedContent;

    public function __construct()
    {

    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function setHttpCode($httpCode)
    {

    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($contentType)
    {

    }

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

    public function getVar($name)
    {
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        }
        throw new InvalidArgumentException('Unknown template var "' . (string) $name . '"');
    }

    public function render($templateName, $vars = null)
    {
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
                    $tplEngine = new TwigView(RESOURCES_DIR . 'template/', $bundleViewsDir, RESOURCES_DIR . 'cache/');
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
                    $tplEngine = new BladeView(RESOURCES_DIR . 'template/', $bundleViewsDir, RESOURCES_DIR . 'cache/');
                } else {
                    throw new MissingTemplateEngineException('The Blade template engine is missing :/ Please add the
                    required dependency : "philo/laravel-blade"');
                }
            } else if (str_ends_with($templateName, '.php')) {
                /**
                 * Use raw php for templates
                 */
                $tplEngine = new PhpView();
            } else {
                throw new InvalidArgumentException('No template engine found for "' . (string)$templateName . '"');
            }

            $this->renderedContent = $tplEngine->render($templateName, $this->vars);
        } else {
            throw new InvalidArgumentException('"' . (string)$templateName . '" template doesn\'t exists');
        }
    }

    /**
     * @return mixed
     */
    public function getRenderedContent()
    {
        return $this->renderedContent;
    }
} 