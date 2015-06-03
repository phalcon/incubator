<?php

namespace Phalcon\Test\Utils;

use Phalcon\Utils\Slug;
use malkusch\phpmock\MockBuilder;
use malkusch\phpmock\phpunit\MockDelegateFunction;
use malkusch\phpmock\phpunit\MockDisabler;
use malkusch\phpmock\phpunit\MockObjectProxy;

class SlugTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function dataStrings()
    {
        return array(
            array(
                "Mess'd up --text-- just (to) stress/test/ ?our! " .
                "`little` \\clean\\ url fun.ction!?-->",
                array(),
                "-",
                'messd-up-text-just-to-stress-test-our-little-clean-url-function'
            ),
            array(
                "Perchè l'erba è verde?",
                "'",
                "-",
                'perche-l-erba-e-verde'
            ), // Italian

            array(
                "Peux-tu m'aider s'il te plaît?",
                "'",
                "-",
                'peux-tu-m-aider-s-il-te-plait'
            ), // French

            array(
                "Tänk efter nu – förr'n vi föser dig bort",
                "-",
                "-",
                'tank-efter-nu-forrn-vi-foser-dig-bort'
            ), // Swedish
            array(
                "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝßàáâãäåæçèéêëìíîïñòóôõöùúûüýÿ",
                array(),
                '-',
                'aaaaaaaeceeeeiiiinooooouuuuyssaaaaaaaeceeeeiiiinooooouuuuyy'
            ),
            array(
                "Custom`delimiter*example",
                array('*', '`'),
                "-",
                'custom-delimiter-example'
            ),
            array(
                "My+Last_Crazy|delimiter/example",
                '',
                ' ',
                'my last crazy delimiter example'
            )
        );
    }

    /**
     * @requires PHP 5.4
     *
     * @expectedException           \Phalcon\Exception
     * @expectedExceptionMessage    iconv module not loaded
     */
    public function testExtensionNotLoaded()
    {
        $mock = $this->getMockBuilder('\malkusch\phpmock\phpunit\MockDelegate')
            ->getMock();

        $functionMockBuilder = new MockBuilder();
        $functionMockBuilder->setNamespace('Phalcon\\Utils')
            ->setName("extension_loaded")
            ->setFunctionProvider(new MockDelegateFunction($mock));

        $functionMock = $functionMockBuilder->build();
        $functionMock->enable();

        $result = $this->getTestResultObject();
        $result->addListener(new MockDisabler($functionMock));

        $iconv = new MockObjectProxy($mock);
        $iconv->expects($this->once())->willReturn(false);
        Slug::generate('test 233');
        $functionMock->disable();
    }

    /**
     * @dataProvider dataStrings
     *
     * @requires     extension iconv
     */
    public function testGenerate($string, $replace, $delimeter, $willReturn)
    {
        $this->assertEquals(Slug::generate($string, $replace, $delimeter), strtolower($willReturn));
    }
}
