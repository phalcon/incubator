<?php

namespace Phalcon\Test\Validation\Validator;

use UnitTester;
use Phalcon\Validation;
use Codeception\TestCase\Test;
use Phalcon\Validation\Validator\CardNumber;

/**
 * \Phalcon\Test\Validation\Validator\IpValidatorTest
 * Tests for Phalcon\Validation\Validator\IpValidator component
 *
 * @copyright (c) 2011-2017 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Michele Angioni <michele.angioni@gmail.com>
 * @package   Phalcon\Test\Mvc\Model\Validator
 * @group     Validation
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class IpValidatorTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    public function testIpValidatorOk()
    {
        $data['ip'] = '192.168.0.1';

        $validation = new Validation();

        $validation->add(
            'ip',
            new \Phalcon\Validation\Validator\IpValidator (
                [
                    'message' => 'The IP is not valid.'
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testIpValidatorFailing()
    {
        $data['ip'] = '192.168.0.1.1';

        $validation = new Validation();

        $validation->add(
            'ip',
            new \Phalcon\Validation\Validator\IpValidator (
                [
                    'message' => 'The IP is not valid.'
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }
}
