<?php
namespace Phalcon\Mvc\View\Engine;

use Phalcon\Mvc\View\Engine;
use Phalcon\Mvc\View\EngineInterface;

/**
 * Phalcon\Mvc\View\Engine\Mustache
 * Adapter to use Mustache library as templating engine
 */
class Mustache extends Engine implements EngineInterface
{

    /**
     * @var \Mustache_Engine
     */
    protected $mustache;

    /**
     * Class constructor.
     *
     * @param \Phalcon\Mvc\ViewInterface $view
     * @param \Phalcon\DiInterface       $dependencyInjector
     */
    public function __construct($view, $dependencyInjector = null)
    {
        $this->mustache = new \Mustache_Engine();

        parent::__construct($view, $dependencyInjector);
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
        if (!isset($params['content'])) {
            $params['content'] = $this->_view->getContent();
        }

        $content = $this->mustache->render(file_get_contents($path), $params);
        if ($mustClean) {
            $this->_view->setContent($content);
        } else {
            echo $content;
        }
    }
}
