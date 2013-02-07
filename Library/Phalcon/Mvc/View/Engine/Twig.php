<?php

namespace Phalcon\Mvc\View\Engine;

use Phalcon\Mvc\View\Engine,
    Phalcon\Mvc\View\EngineInterface;

/**
 * Phalcon\Mvc\View\Engine\Twig
 *
 * Adapter to use Twig library as templating engine
 */
class Twig extends Engine implements EngineInterface
{

    protected $_twig;

    /**
     * Phalcon\Mvc\View\Engine\Twig constructor
     *
     * @param \Phalcon\Mvc\ViewInterface $view
     * @param \Phalcon\DiInterface $di
     */
    public function __construct($view,  $di)
    {
        $loader = new Twig_Loader_Filesystem($view->getViewsDir());
        $this->_twig = new Twig_Environment($loader);
        $this->_registryFunctions();
        parent::__construct($view, $di);
    }

    /**
     * Registers common function in Twig
     *
     * @param \Phalcon\Mvc\ViewInterface $view
     */
    private function _registryFunctions($view)
    {
        $functions = array(
             new \Twig_SimpleFunction('content', function() use ($view) {
                return $view->getContent();
            }),
            new \Twig_SimpleFunction('linkTo', function($parameters, $text = null) {
                return \Phalcon\Tag::linkTo($parameters, $text);
            }),
            new \Twig_SimpleFunction('textField', function($parameters) {
                return \Phalcon\Tag::textField($parameters);
            }),
            new \Twig_SimpleFunction('passwordField', function($parameters) {
                return \Phalcon\Tag::passwordField($parameters);
            }),
            new \Twig_SimpleFunction('hiddenField', function($parameters) {
                return \Phalcon\Tag::hiddenField($parameters);
            }),
            new \Twig_SimpleFunction('fileField', function($parameters) {
                return \Phalcon\Tag::fileField($parameters);
            }),
            new \Twig_SimpleFunction('checkField', function($parameters) {
                return \Phalcon\Tag::checkField($parameters);
            }),
            new \Twig_SimpleFunction('radioField', function($parameters) {
                return \Phalcon\Tag::radioField($parameters);
            }),
            new \Twig_SimpleFunction('submitButton', function($parameters) {
                return \Phalcon\Tag::submitButton($parameters);
            }),
            new \Twig_SimpleFunction('selectStatic', function($parameters, $data=null) {
                return \Phalcon\Tag::selectStatic($parameters, $data);
            }),
            new \Twig_SimpleFunction('select', function($parameters, $data=null) {
                return \Phalcon\Tag::select($parameters, $data);
            }),
            new \Twig_SimpleFunction('textArea', function($parameters) {
                return \Phalcon\Tag::textArea($parameters);
            }),
            new \Twig_SimpleFunction('form', function($parameters=null) {
                return \Phalcon\Tag::form($parameters);
            }),
            new \Twig_SimpleFunction('endForm', function() {
                return \Phalcon\Tag::endForm();
            }),
            new \Twig_SimpleFunction('getTitle', function() {
                return \Phalcon\Tag::getTitle();
            }),
            new \Twig_SimpleFunction('getTitle', function() {
                return \Phalcon\Tag::getTitle();
            }),
            new \Twig_SimpleFunction('stylesheetLink', function($parameters=null, $local=null) {
                return \Phalcon\Tag::stylesheetLink($parameters, $local);
            }),
            new \Twig_SimpleFunction('javascriptInclude', function($parameters=null, $local=null) {
                return \Phalcon\Tag::javascriptInclude($parameters, $local);
            }),
            new \Twig_SimpleFunction('image', function($parameters) {
                return \Phalcon\Tag::image($parameters);
            }),
            new \Twig_SimpleFunction('friendlyTitle', function($text, $separator=null, $lowercase=null) {
                return \Phalcon\Tag::friendlyTitle($text, $separator, $lowercase);
            })
        );

        foreach ($functions as $function) {
            $this->_twig->addFunction($function);
        }
    }

    /**
     * Renders a view
     *
     * @param string $path
     * @param array $params
     * @param boolean $mustClean
     */
    public function render($path, $params, $mustClean=false)
    {
        $view = $this->_view;
        if (!isset($params['content'])) {
            $params['content'] = $view->getContent();
        }

        if (!isset($params['view'])) {
            $params['view'] = $view;
        }

        $relativePath = str_replace($view->getViewsDir(), '', $path);

        $content = $this->_twig->render($relativePath, $params);
        if ($mustClean) {
            $this->_view->setContent($content);
        } else {
            echo $content;
        }
    }

}
