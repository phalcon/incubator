<?php
namespace Phalcon\Mvc\View\Engine;

use Phalcon\Mvc\ViewBaseInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\View\Engine\Volt\MinifierCompiler;

/**
 * Phalcon\Mvc\View\Engine\VoltMinifier
 * Adapter using a html-minifying compiler for volt templates. Minification is
 * performed on the compiled templates before they are stored in the cache.
 */
class VoltMinifier extends Volt
{
    /**
     * @inheritdoc
     */
    public function __construct(ViewBaseInterface $view, DiInterface $dependencyInjector = null)
    {
        parent::__construct($view, $dependencyInjector);

        $this->_compiler = new MinifierCompiler($view);
        $this->_compiler->setDi($dependencyInjector);
    }

    /**
     * As the compiler is set in the constructor, the options will not be set automatically,
     * so let setOptions pass the options to the compiler.
     *
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);
        if (is_array($options)) {
            $this->_compiler->setOptions($options);
        }
    }

    /**
     * Enable/disable minification
     * @param bool $shouldMinify
     */
    public function setMinify($shouldMinify)
    {
        if ($this->_compiler instanceof MinifierCompiler) {
            $this->_compiler->setMinify($shouldMinify);
        }
    }
}
