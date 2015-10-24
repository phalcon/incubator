<?php

namespace Phalcon\Test\Mvc\Model\Validator;

use Phalcon\Test\Mvc\Model\Validator\Stubs\BetweenModel;
use Phalcon\Test\Mvc\Model\Validator\Stubs\BetweenIncorrectField;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Di;
use Phalcon\DiInterface;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Mvc\Model\Validator\BetweenTest
 * Tests for Phalcon\Mvc\Model\Validator\Between component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Ilya Gusev <mail@igusev.ru>
 * @package   Phalcon\Test\Mvc\Model\Validator
 * @group     DbValidation
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class BetweenTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var DiInterface
     */
    protected $previousDependencyInjector;

    /**
     * executed before each test
     */
    protected function _before()
    {
        $this->previousDependencyInjector = Di::getDefault();

        $di = new Di();
        $di->set('modelsManager', new Manager());

        if ($this->previousDependencyInjector instanceof DiInterface) {
            Di::setDefault($di);
        }
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
        if ($this->previousDependencyInjector instanceof DiInterface) {
            Di::setDefault($this->previousDependencyInjector);
        } else {
            Di::reset();
        }
    }

    /**
     * @expectedException        \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage Field name must be a string
     */
    public function testShouldCatchExceptionWhenPassedIncorrectFieldType()
    {
        $obj = new BetweenIncorrectField();

        $obj->validation();
    }

    public function testShouldValidateIfAllowedEmpty()
    {
        $obj = new BetweenModel();

        $obj->min = 1;
        $obj->max = 3;
        $obj->allowEmpty = true;

        $this->assertEquals(true, $obj->validation());
    }

    /**
     * @expectedException        \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage A minimum and maximum must be set
     */
    public function testShouldCatchExceptionWithoutMinParam()
    {
        $obj = new BetweenModel();

        $obj->max = 3;
        $obj->validation();
    }

    /**
     * @expectedException        \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage A minimum and maximum must be set
     */
    public function testShouldCatchExceptionWithoutMinAndMaxParam()
    {
        $obj = new BetweenModel();

        $obj->validation();
    }

    /**
     * @expectedException        \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage A minimum and maximum must be set
     */
    public function testShouldCatchExceptionWithoutMaxParam()
    {
        $obj = new BetweenModel();

        $obj->min = 1;

        $obj->validation();
    }

    public function testShouldReceiveDefaultMessage()
    {
        $obj = new BetweenModel();

        $obj->min = 1;
        $obj->max = 2;
        $obj->position = 3;

        $obj->validation();
        $messages = $obj->getMessages();
        $this->assertEquals(
            $messages[0]->getMessage(),
            'position is not between a valid range'
        );
    }

    public function testShouldReceiveCustomMessage()
    {
        $obj = new BetweenModel();

        $obj->min = 1;
        $obj->max = 2;
        $obj->position = 3;
        $obj->message = 'test 123';

        $obj->validation();
        $messages = $obj->getMessages();
        $this->assertEquals(
            $messages[0]->getMessage(),
            'test 123'
        );
    }

    /**
     * @dataProvider providerBetween
     * @param int $min
     * @param int $max
     * @param int $position
     * @param boolean $willReturn
     */
    public function testShouldValidateBetweenUsingModelValidator($min, $max, $position, $willReturn)
    {
        $obj = new BetweenModel();

        $obj->position = $position;
        $obj->min = $min;
        $obj->max = $max;

        $this->assertEquals($willReturn, $obj->validation());
    }

    public function providerBetween()
    {
        return [
            [1, 10, 3, true],
            [1, 10, 4, true],
            [1, 10, -1, false],
            [1, 10, 0, false],
            [-1, 1, 0, true],
        ];
    }
}
