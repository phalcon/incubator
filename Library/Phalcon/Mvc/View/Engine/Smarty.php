<?php
namespace Phalcon\Mvc\View\Engine;

use Phalcon\Mvc\View\Engine;
use Phalcon\Mvc\View\EngineInterface;
use Phalcon\DiInterface;

/**
 * Phalcon\Mvc\View\Engine\Smarty
 * Adapter to use Smarty library as templating engine
 */
class Smarty extends Engine implements EngineInterface
{

    /**
     * @var \Smarty
     */
    protected $smarty;

    /**
     * {@inheritdoc}
     *
     * @param \Phalcon\Mvc\ViewInterface $view
     * @param \Phalcon\DiInterface       $di
     */
    public function __construct($view, \Phalcon\DiInterface $dependencyInjector = null)
    {
        $this->smarty               = new \Smarty();
        $this->smarty->template_dir = '.';
        $this->smarty->compile_dir  = SMARTY_DIR . 'templates_c';
        $this->smarty->config_dir   = SMARTY_DIR . 'configs';
        $this->smarty->cache_dir    = SMARTY_DIR . 'cache';
        $this->smarty->caching      = false;
        $this->smarty->debugging    = true;

        parent::__construct($view, $dependencyInjector);
    }

    /**
     * {@inheritdoc}
     *
     * @param string  $path
     * @param array   $params
     * @param boolean $mustClean
     */
    public function render($path, $params, $mustClean = null)
    {
        if (!isset($params['content'])) {
            $params['content'] = $this->_view->getContent();
        }
        foreach ($params as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        $this->_view->setContent($this->smarty->fetch($path));
    }

    /**
     * Set Smarty's options
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach ($options as $k => $v) {
            $this->smarty->$k = $v;
        }
    }
    
    /**
     * Get Smarty object
     *
     * @return \Smarty
     */
    public function getSmarty()
    {
        return $this->smarty;
    }
}
