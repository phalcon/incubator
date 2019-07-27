<?php
namespace Phalcon\Mvc\View\Engine;

use Phalcon\DiInterface;
use Phalcon\Mvc\View\Engine;
use Phalcon\Mvc\View\EngineInterface;
use Phalcon\Mvc\ViewBaseInterface;

/**
 * Phalcon\Mvc\View\Engine\Twig
 * Adapter to use Twig library as templating engine
 */
class Twig extends Engine implements EngineInterface
{
    /**
     * @var \Phalcon\Mvc\View\Engine\Twig\Environment
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
        $loader = new \Twig_Loader_Filesystem(
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
     * @param \Phalcon\Mvc\ViewInterface $view
     * @param \Phalcon\DiInterface       $di
     * @param array                      $userFunctions
     */
    protected function registryFunctions($view, DiInterface $di, $userFunctions = [])
    {
        $options = ['is_safe' => ['html']];

        $functions = [
            new \Twig_SimpleFunction(
                'content',
                function () use ($view) {
                    return $view->getContent();
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'partial',
                function ($partialPath, $params = null) use ($view) {
                    return $view->partial($partialPath, $params);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'linkTo',
                function ($parameters, $text = null, $local = true) {
                    return \Phalcon\Tag::linkTo($parameters, $text, $local);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'textField',
                function ($parameters) {
                    return \Phalcon\Tag::textField($parameters);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'passwordField',
                function ($parameters) {
                    return \Phalcon\Tag::passwordField($parameters);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'hiddenField',
                function ($parameters) {
                    return \Phalcon\Tag::hiddenField($parameters);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'fileField',
                function ($parameters) {
                    return \Phalcon\Tag::fileField($parameters);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'checkField',
                function ($parameters) {
                    return \Phalcon\Tag::checkField($parameters);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'radioField',
                function ($parameters) {
                    return \Phalcon\Tag::radioField($parameters);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'submitButton',
                function ($parameters) {
                    return \Phalcon\Tag::submitButton($parameters);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'selectStatic',
                function ($parameters, $data = []) {
                    return \Phalcon\Tag::selectStatic($parameters, $data);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'select',
                function ($parameters, $data = []) {
                    return \Phalcon\Tag::select($parameters, $data);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'textArea',
                function ($parameters) {
                    return \Phalcon\Tag::textArea($parameters);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'form',
                function ($parameters = []) {
                    return \Phalcon\Tag::form($parameters);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'endForm',
                function () {
                    return \Phalcon\Tag::endForm();
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'getTitle',
                function () {
                    return \Phalcon\Tag::getTitle();
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'stylesheetLink',
                function ($parameters = null, $local = true) {
                    return \Phalcon\Tag::stylesheetLink($parameters, $local);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'javascriptInclude',
                function ($parameters = null, $local = true) {
                    return \Phalcon\Tag::javascriptInclude($parameters, $local);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'image',
                function ($parameters = null, $local = true) {
                    return \Phalcon\Tag::image($parameters, $local);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'friendlyTitle',
                function ($text, $separator = "-", $lc = true, $replace = null) {
                    return \Phalcon\Tag::friendlyTitle($text, $separator, $lc, $replace);
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'getDocType',
                function () {
                    return \Phalcon\Tag::getDocType();
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'getSecurityToken',
                function () use ($di) {
                    return $di->get("security")->getToken();
                },
                $options
            ),
            new \Twig_SimpleFunction(
                'getSecurityTokenKey',
                function () use ($di) {
                    return $di->get("security")->getTokenKey();
                },
                $options
            ),
            new \Twig_SimpleFunction(
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
     * @return \Phalcon\Mvc\View\Engine\Twig\Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }
}
