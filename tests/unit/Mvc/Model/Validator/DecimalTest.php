<?php

namespace Phalcon\Test\Mvc\Model\Validator;

use Phalcon\Test\Mvc\Model\Validator\Stubs\DecimalModel;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\Manager;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Mvc\Model\Validator\CardNumberTest
 * Tests for Phalcon\Mvc\Model\Validator\CardNumber component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Nikita Vershinin <endeveit@gmail.com>
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
class DecimalTest extends Test
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
     * @expectedExceptionMessage A number of decimal places must be set
     */
    public function testShouldCatchExceptionWhenMissedPlacesInDecimalValidation()
    {
        $entity = $this->getEntity();
        $entity->places = null;

        $entity->validation();
    }

    public function testShouldValidateUsingPlacesInDecimalValidation()
    {
        $entity = $this->getEntity();
        $entity->field = '2.1';

        $this->assertEquals(false, $entity->validation());

        $entity = $this->getEntity();

        $this->assertEquals(true, $entity->validation());
    }

    public function testShouldValidateUsingDigitsInDecimalValidation()
    {
        $entity = $this->getEntity();
        $entity->digits = 2;

        $this->assertEquals(false, $entity->validation());

        $entity = $this->getEntity();
        $entity->digits = 1;

        $this->assertEquals(true, $entity->validation());
    }

    /**
     * @return DecimalModel
     */
    protected function getEntity()
    {
        $entity = new DecimalModel();
        $entity->field = '2.12';
        $entity->places = 2;

        return $entity;
    }
}
