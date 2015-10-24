<?php

namespace Phalcon\Test\Utils;

use Phalcon\Utils\Slug;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Utils\SlugTest
 * Tests for Phalcon\Utils\Slug component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Ilya Gusev <mail@igusev.ru>
 * @package   Phalcon\Test\Utils
 * @group     Utils
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class SlugTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * executed before each test
     */
    protected function _before()
    {
        if (!extension_loaded('iconv')) {
            $this->markTestSkipped(
                'The iconv module is not available.'
            );
        }
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    /**
     * @dataProvider providerStrings
     * @param string $string
     * @param mixed $replace
     * @param string $delimiter
     * @param string $willReturn
     */
    public function testGenerateSlug($string, $replace, $delimiter, $willReturn)
    {
        $this->assertEquals(Slug::generate($string, $replace, $delimiter), strtolower($willReturn));
    }

    public function providerStrings()
    {
        return [
            [
                "Mess'd up --text-- just (to) stress/test/ ?our! " . "`little` \\clean\\ url fun.ction!?-->",
                [],
                "-",
                'mess-d-up-text-just-to-stress-test-our-little-clean-url-fun-ction'
            ],
            [
                "Perchè l'erba è verde?",
                "'",
                "-",
                'perche-l-erba-e-verde'
            ], // Italian
            [
                "Peux-tu m'aider s'il te plaît?",
                "'",
                "-",
                'peux-tu-m-aider-s-il-te-plait'
            ], // French
            [
                "Tänk efter nu – förr'n vi föser dig bort",
                "-",
                "-",
                'tank-efter-nu-forr-n-vi-foser-dig-bort'
            ], // Swedish
            [
                "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝßàáâãäåæçèéêëìíîïñòóôõöùúûüýÿ",
                [],
                '-',
                'aaaaaaaeceeeeiiiinooooouuuuyssaaaaaaaeceeeeiiiinooooouuuuyy'
            ],
            [
                "Custom`delimiter*example",
                ['*' => " replace "],
                "-",
                'custom-delimiter-replace-example'
            ],
            [
                "My+Last_Crazy|delimiter/example",
                '',
                ' ',
                'my last crazy delimiter example'
            ],
            [
                "What does it mean yapılır in Turkish",
                ['ı' => 'i'],
                "-",
                "what-does-it-mean-yapilir-in-turkish"
            ] // Turkish
        ];
    }
}
