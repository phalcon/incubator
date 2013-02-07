<?php

namespace Phalcon\Mvc\View\Engine;

use Phalcon\Mvc\View\Engine,
    Phalcon\Mvc\View\EngineInterface;

/**
 * Phalcon\Mvc\View\Engine\Mustache
 *
 * Adapter to use Mustache library as templating engine
 */
class Mustache extends Engine implements EngineInterface
{

    protected $_mustache;

    protected $_params;

    /**
     * Phalcon\Mvc\View\Engine\Mustache constructor
     *
     * @param \Phalcon\Mvc\ViewInterface $view
     * @param \Phalcon\DiInterface $di
     */
    public function __construct($view,  $di)
    {
        $this->_mustache = new \Mustache_Engine();
        parent::__construct($view, $di);
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
        if (!isset($params['content'])) {
            $params['content'] = $this->_view->getContent();
        }

        $content = $this->_mustache->render(file_get_contents($path), $params);
        if ($mustClean) {
            $this->_view->setContent($content);
        } else {
            echo $content;
        }
    }

}