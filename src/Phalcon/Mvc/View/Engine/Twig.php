<?php
namespace Phalcon\Mvc\View\Engine;

use Phalcon\DiInterface;
use Phalcon\Mvc\View\Engine;
use Phalcon\Mvc\View\Engine\Twig\Environment;
use Phalcon\Mvc\View\EngineInterface;
use Phalcon\Mvc\ViewBaseInterface;
use Phalcon\Mvc\ViewInterface;
use Phalcon\Tag;
use Twig_Loader_Filesystem;
use Twig_SimpleFunction;

/**
 * Phalcon\Mvc\View\Engine\Twig
 * Adapter to use Twig library as templating engine
 */
class Twig extends Engine implements EngineInterface
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * {@inheritdoc}
     *
     * @param ViewBaseInterface $view
     * @param DiInterface       $di
     * @param array             $options
     * @param array             $userFunctions
     */
    public function __construct(
        ViewBaseInterface $view,
        DiInterface $di = null,
        $options = [],
        $userFunctions = []
    ) {
        $loader = new Twig_Loader_Filesystem(
            $view->getViewsDir()
        );

        $this->twig = new Twig\Environment($di, $loader, $options);

        $this->twig->addExtension(
            new Twig\CoreExtension()
        );

        $this->registryFunctions($view, $di, $userFunctions);

        parent::__construct($view, $di);
    }

    /**
     * Registers common function in Twig
     *
     * @param ViewInterface $view
     * @param DiInterface $di
     * @param array                      $userFunctions
     */
    protected function registryFunctions($view, DiInterface $di, $userFunctions = [])
    {
        $options = ['is_safe' => ['html']];

        $functions = [
            new Twig_SimpleFunction(
                'content',
                function () use ($view) {
                    return $view->getContent();
                },
                $options
            ),
            new Twig_SimpleFunction(
                'partial',
                function ($partialPath, $params = null) use ($view) {
                    return $view->partial($partialPath, $params);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'linkTo',
                function ($parameters, $text = null, $local = true) {
                    return Tag::linkTo($parameters, $text, $local);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'textField',
                function ($parameters) {
                    return Tag::textField($parameters);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'passwordField',
                function ($parameters) {
                    return Tag::passwordField($parameters);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'hiddenField',
                function ($parameters) {
                    return Tag::hiddenField($parameters);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'fileField',
                function ($parameters) {
                    return Tag::fileField($parameters);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'checkField',
                function ($parameters) {
                    return Tag::checkField($parameters);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'radioField',
                function ($parameters) {
                    return Tag::radioField($parameters);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'submitButton',
                function ($parameters) {
                    return Tag::submitButton($parameters);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'selectStatic',
                function ($parameters, $data = []) {
                    return Tag::selectStatic($parameters, $data);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'select',
                function ($parameters, $data = []) {
                    return Tag::select($parameters, $data);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'textArea',
                function ($parameters) {
                    return Tag::textArea($parameters);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'form',
                function ($parameters = []) {
                    return Tag::form($parameters);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'endForm',
                function () {
                    return Tag::endForm();
                },
                $options
            ),
            new Twig_SimpleFunction(
                'getTitle',
                function () {
                    return Tag::getTitle();
                },
                $options
            ),
            new Twig_SimpleFunction(
                'stylesheetLink',
                function ($parameters = null, $local = true) {
                    return Tag::stylesheetLink($parameters, $local);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'javascriptInclude',
                function ($parameters = null, $local = true) {
                    return Tag::javascriptInclude($parameters, $local);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'image',
                function ($parameters = null, $local = true) {
                    return Tag::image($parameters, $local);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'friendlyTitle',
                function ($text, $separator = "-", $lc = true, $replace = null) {
                    return Tag::friendlyTitle($text, $separator, $lc, $replace);
                },
                $options
            ),
            new Twig_SimpleFunction(
                'getDocType',
                function () {
                    return Tag::getDocType();
                },
                $options
            ),
            new Twig_SimpleFunction(
                'getSecurityToken',
                function () use ($di) {
                    return $di->get("security")->getToken();
                },
                $options
            ),
            new Twig_SimpleFunction(
                'getSecurityTokenKey',
                function () use ($di) {
                    return $di->get("security")->getTokenKey();
                },
                $options
            ),
            new Twig_SimpleFunction(
                'url',
                function ($route) use ($di) {
                    return $di->get("url")->get($route);
                },
                $options
            )
        ];

        if (!empty($userFunctions)) {
            $functions = array_merge($functions, $userFunctions);
        }

        foreach ($functions as $function) {
            $this->twig->addFunction($function);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string  $path
     * @param array   $params
     * @param boolean $mustClean
     */
    public function render($path, $params, $mustClean = false)
    {
        $view = $this->_view;
        if (!isset($params['content'])) {
            $params['content'] = $view->getContent();
        }

        if (!isset($params['view'])) {
            $params['view'] = $view;
        }

        $relativePath = str_replace(
            $view->getViewsDir(),
            '',
            $path
        );

        $content = $this->twig->render($relativePath, $params);

        if ($mustClean) {
            $this->_view->setContent($content);
        } else {
            echo $content;
        }
    }

    /**
     * Returns Twig environment object.
     *
     * @return Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }
}
