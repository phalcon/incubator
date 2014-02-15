<?php
/**
 * Phalcon Framework
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phalconphp.com so we can send you a copy immediately.
 *
 * @author Nikita Vershinin <endeveit@gmail.com>
 */
namespace Phalcon\Mvc\View\Engine\Twig\Nodes;

/**
 * \Phalcon\Mvc\View\Engine\Twig\Nodes\Assets
 * Twig node object that compiles "assets" tag in template.
 */
class Assets extends \Twig_Node
{

    /**
     * {@inheritdoc}
     *
     * @param \Twig_Compiler $compiler
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this)
            ->write('$this->env->getDI()->get(\'assets\')->')
            ->raw($this->getAttribute('methodName'))
            ->write('(');

        $nbArgs = count($this->getNode('arguments'));
        $i = 0;

        foreach ($this->getNode('arguments') as $argument) {
            $compiler->subcompile($argument);

            if (++$i < $nbArgs) {
                $compiler->raw(', ');
            }
        }

        $compiler->write(");\n");
    }
}
