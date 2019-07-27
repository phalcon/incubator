<?php

namespace Phalcon\Test\Unit5x\Validation\Validator;

use ReflectionExtension;
use Phalcon\Validation;
use Phalcon\Validation\Validator\MongoId;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use UnitTester;

/**
 * \Phalcon\Test\Validation\Validator\MongoIdTest
 * Tests for Phalcon\Validation\Validator\MongoId component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Anton Kornilov <kachit@yandex.ru>
 * @package   Phalcon\Test\Validation\Validator
 * @group     Validation
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class MongoIdTest extends Test
{
    const MIN_PECL_VERSION = '1.5.2';

    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var MongoId
     */
    private $testable;

    /**
     * @var Validation
     */
    private $validation;

    /**
     * executed before each test
     */
    protected function _before()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped(
                'The Mongo extension is not available.'
            );
        }

        $ext = new ReflectionExtension('mongo');

        if (!version_compare($ext->getVersion(), self::MIN_PECL_VERSION, '>=')) {
            $this->markTestSkipped(
                sprintf(
                    "Your mongo extension version isn't compatible with Incubator, download the latest at: %s",
                    'https://docs.mongodb.org/ecosystem/drivers/php/'
                )
            );
        }

        $this->testable = new MongoId();
        $this->validation = new Validation();
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    public function testInvalidMongoIdValue()
    {
        $array = [
            'id' => 123,
        ];

        $this->validation->add(
            'id',
            $this->testable
        );

        $messages = $this->validation->validate($array);

        $this->assertCount(
            1,
            $messages
        );

        $this->assertEquals(
            'MongoId is not valid',
            $messages[0]->getMessage()
        );

        $this->assertEquals(
            'MongoId',
            $messages[0]->getType()
        );
    }

    public function testValidMongoIdValue()
    {
        $array = [
            'id' => '561824e063e702bc1900002a',
        ];

        $this->validation->add(
            'id',
            $this->testable
        );

        $messages = $this->validation->validate($array);

        $this->assertCount(
            0,
            $messages
        );
    }

    public function testEmptyMongoIdValue()
    {
        $array = [
            'id' => '',
        ];

        $this->validation->add(
            'id',
            $this->testable
        );

        $messages = $this->validation->validate($array);

        $this->assertCount(
            1,
            $messages
        );

        $this->assertEquals(
            'MongoId is not valid',
            $messages[0]->getMessage()
        );

        $this->assertEquals(
            'MongoId',
            $messages[0]->getType()
        );
    }

    public function testEmptyMongoIdValueWithAllowEmptyOption()
    {
        $array = [
            'id' => '',
        ];

        $this->testable->setOption('allowEmpty', true);

        $this->validation->add(
            'id',
            $this->testable
        );

        $messages = $this->validation->validate($array);

        $this->assertCount(
            0,
            $messages
        );
    }
}
