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
        parent::__construct($view, $di);
    }

    /**
     * Renders a view
     *
     * @param string $path
     * @param array $params
     */
    public function render($path, $params)
    {
        $view = $this->_view;
        if (!isset($params['content'])) {
            $params['content'] = $view->getContent();
        }
        if (!isset($params['view'])) {
            $params['view'] = $view;
        }
        $relativePath = str_replace($view->getViewsDir(), '', $path);
        $this->_view->setContent($this->_twig->render($relativePath, $params));
    }

}
