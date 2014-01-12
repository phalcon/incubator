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

/**
 * \Phalcon\Mvc\View\Engine\Twig\CoreExtension
 * Core extension for Twig engine.
 * Currently supports only work with \Phalcon\Assets\Manager.
 */
class CoreExtension extends \Twig_Extension
{

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'phalcon-core-extension';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getFunctions()
    {
        $options = array(
            'needs_environment' => true,
            'pre_escape'        => 'html',
            'is_safe'           => array('html'),
        );

        return array(
            'assetsOutputCss' => new \Twig_Function_Method($this, 'functionAssetsOutputCss', $options),
            'assetsOutputJs'  => new \Twig_Function_Method($this, 'functionAssetsOutputJs', $options),
        );
    }

    /**
     * Returns string with CSS.
     *
     * @param  \Phalcon\Mvc\View\Engine\Twig\Environment $env
     * @return string
     */
    public function functionAssetsOutputCss(Environment $env)
    {
        return $this->getAssetsOutput($env, 'outputCss');
    }

    /**
     * Returns string with JS.
     *
     * @param  \Phalcon\Mvc\View\Engine\Twig\Environment $env
     * @return string
     */
    public function functionAssetsOutputJs(Environment $env)
    {
        return $this->getAssetsOutput($env, 'outputJs');
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return array(
            new TokenParsers\Assets(),
        );
    }

    /**
     * Proxy method that handles return of assets instead of instant output.
     *
     * @param  \Phalcon\Mvc\View\Engine\Twig\Environment $env
     * @param  string                                    $method
     * @return string
     */
    protected function getAssetsOutput(Environment $env, $method)
    {
        $env->getDi()->get('assets')->useImplicitOutput(false);
        $result = $env->getDi()->get('assets')->$method();
        $env->getDi()->get('assets')->useImplicitOutput(true);

        return $result;
    }
}
