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
 * @group     utils
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
     * @param string $expected
     */
    public function testGenerateSlug($string, $replace, $delimiter, $expected)
    {
        $this->assertEquals(
            $expected,
            Slug::generate($string, $replace, $delimiter),
            'Two strings are equals',
            0.0,
            10,
            false,
            true
        );
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
            ], // Turkish
            [
                'Àà Ââ Ææ Ää Çç Éé Èè Êê Ëë Îî Ïï Ôô Œœ Öö Ùù Ûû Üü Ÿÿ',
                [],
                '-',
                'aa-aa-aeae-aa-cc-ee-ee-ee-ee-ii-ii-oo-oeoe-oo-uu-uu-uu-yy'
            ],
            [
                'а б в г д е ё ж з и й к л м н о п р с т у ф х ц ч ш щ ъ ы ь э ю я',
                [],
                '-',
                'a-b-v-g-d-e-e-z-z-i-j-k-l-m-n-o-p-r-s-t-u-f-h-c-c-s-s-y-e-u-a'
            ], // Russian
            [
                'Keramik og stentøj Populære kategorier',
                [],
                '-',
                'keramik-og-stentoj-populaere-kategorier'
            ], // Danish
        ];
    }
}
