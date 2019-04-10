<?php

namespace Phalcon\Test\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Test\Codeception\UnitTestCase as Test;

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
    public function testIpValidatorOk()
    {
        $data['ip'] = '192.168.0.1';

        $validation = new Validation();

        $validation->add(
            'ip',
            new \Phalcon\Validation\Validator\IpValidator(
                [
                    'message' => 'The IP is not valid.',
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            0,
            $messages
        );
    }

    public function testIpValidatorFailing()
    {
        $data['ip'] = '192.168.0.1.1';

        $validation = new Validation();

        $validation->add(
            'ip',
            new \Phalcon\Validation\Validator\IpValidator(
                [
                    'message' => 'The IP is not valid.',
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            1,
            $messages
        );
    }
}
