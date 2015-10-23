<?php

namespace Phalcon\Test\Mvc\Model\Validator;

use Phalcon\Test\Mvc\Model\Validator\Stubs\CardNumberModel;
use Phalcon\Test\Mvc\Model\Validator\Stubs\CardNumberCollection;
use Phalcon\Test\Mvc\Model\Validator\Stubs\CardNumberIncorrectField;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Phalcon\Mvc\Collection\Manager as CollectionManager;
use Phalcon\Di;
use Phalcon\DiInterface;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Mvc\Model\Validator\CardNumberTest
 * Tests for Phalcon\Mvc\Model\Validator\CardNumber component
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
class CardNumberTest extends Test
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
        $di->set('modelsManager', new ModelManager());
        $di->set('collectionManager', new CollectionManager());

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
     * @dataProvider providerCards
     * @param mixed $type
     * @param mixed $cardnumber
     * @param boolean $willReturn
     */
    public function testShouldValidateCardNumberForModel($type, $cardnumber, $willReturn)
    {
        $obj = new CardNumberModel();

        $obj->cardnumber = $cardnumber;

        if ($type || $type === 0) {
            $obj->type = $type;
        }

        $this->assertEquals($willReturn, $obj->validation());
    }

    /**
     * @dataProvider providerCards
     * @param mixed $type
     * @param mixed $cardnumber
     * @param boolean $willReturn
     */
    public function testShouldValidateCardNumberForCollection($type, $cardnumber, $willReturn)
    {
        $obj = new CardNumberCollection();

        $obj->cardnumber = $cardnumber;

        if ($type || $type === 0) {
            $obj->type = $type;
        }

        $this->assertEquals($willReturn, $obj->validation());
    }

    /**
     * @expectedException        \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage Incorrect type specifier
     */
    public function testShouldCatchExceptionWhenValidateIncorrectCardType()
    {
        $obj = new CardNumberModel();

        $obj->type = 500;
        $obj->cardnumber = 1270338206812535;

        $obj->validation();
    }

    /**
     * @expectedException        \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage Field name must be a string
     */
    public function testShouldCatchExceptionWhenValidateIncorrectCardField()
    {
        $obj = new CardNumberIncorrectField();

        $obj->validation();
    }

    public function providerCards()
    {
        return include INCUBATOR_FIXTURES . 'Validation/card_number.php';
    }
}
