<?php

namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Manager;
use Phalcon\DI;

class BetweenTest extends \PHPUnit_Framework_TestCase
{
    public function dataBetween()
    {
        return array(
            array(
                1,
                10,
                3,
                true
            ),
            array(
                1,
                10,
                4,
                true
            ),
            array(
                1,
                10,
                -1,
                false
            ),
            array(
                1,
                10,
                0,
                false
            ),
            array(
                -1,
                1,
                0,
                true
            ),
        );
    }

    /**
     * @dataProvider dataBetween
     */
    public function testValidate($min, $max, $position, $willReturn)
    {
        $di = New DI();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->position = $position;
        $obj->min = $min;
        $obj->max = $max;
        $this->assertEquals($willReturn, $obj->validation());
    }
}
