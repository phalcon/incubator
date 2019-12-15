<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Nikita Vershinin <endeveit@gmail.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Mvc\View\Engine\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * \Phalcon\Mvc\View\Engine\Twig\CoreExtension
 * Core extension for Twig engine.
 * Currently supports only work with \Phalcon\Assets\Manager.
 */
class CoreExtension extends AbstractExtension
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
        $options = [
            'needs_environment' => true,
            'pre_escape'        => 'html',
            'is_safe'           => ['html'],
        ];

        return [
            'assetsOutputCss' => new TwigFunction(
                'assetsOutputCss',
                [$this, 'functionAssetsOutputCss'],
                $options
            ),
            'assetsOutputJs' => new TwigFunction(
                'assetsOutputJs',
                [$this, 'functionAssetsOutputJs'],
                $options
            )
        ];
    }

    /**
     * Returns string with CSS.
     *
     * @param  \Phalcon\Mvc\View\Engine\Twig\Environment $env
     * @param  string|null                               $options Assets CollectionName
     * @return string
     */
    public function functionAssetsOutputCss(Environment $env, $options = null)
    {
        return $this->getAssetsOutput($env, 'outputCss', $options);
    }

    /**
     * Returns string with JS.
     *
     * @param  \Phalcon\Mvc\View\Engine\Twig\Environment $env
     * @param  string|null                               $options Assets CollectionName
     * @return string
     */
    public function functionAssetsOutputJs(Environment $env, $options = null)
    {
        return $this->getAssetsOutput($env, 'outputJs', $options);
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return [new TokenParsers\Assets()];
    }

    /**
     * Proxy method that handles return of assets instead of instant output.
     *
     * @param  \Phalcon\Mvc\View\Engine\Twig\Environment $env
     * @param  string                                    $method
     * @param  string|null                               $options Assets CollectionName
     * @return string
     */
    protected function getAssetsOutput(Environment $env, $method, $options = null)
    {
        $env->getDi()->get('assets')->useImplicitOutput(false);
        $result = $env->getDi()->get('assets')->$method($options);
        $env->getDi()->get('assets')->useImplicitOutput(true);

        return $result;
    }
}
