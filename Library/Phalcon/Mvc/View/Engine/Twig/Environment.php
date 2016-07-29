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
namespace Phalcon\Mvc\View\Engine\Twig;

use Phalcon\DiInterface;

/**
 * \Phalcon\Mvc\View\Engine\Twig\Environment
 * Twig environment that uses internal dependency injector.
 */
class Environment extends \Twig_Environment
{
    /**
     * Internal dependency injector.
     *
     * @var \Phalcon\DiInterface
     */
    protected $di = null;

    /**
     * {@inheritdoc}
     *
     * @param \Phalcon\DiInterface  $di
     * @param \Twig_LoaderInterface $loader
     * @param array                 $options
     */
    public function __construct(DiInterface $di, \Twig_LoaderInterface $loader = null, $options = [])
    {
        $this->di = $di;

        parent::__construct($loader, $options);
    }

    /**
     * Returns the internal dependency injector.
     *
     * @return \Phalcon\DiInterface
     */
    public function getDi()
    {
        return $this->di;
    }
}
