<?php
namespace Phalcon\Test\Mvc\Model\Validator;

use Phalcon\Di;
use Phalcon\Mvc\Model\Manager;

class DecimalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException        \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage A number of decimal places must be set
     */
    public function testMissedPlacesException()
    {
        $entity = $this->getEntity();
        $entity->places = null;

        $entity->validation();
    }

    public function testPlaces()
    {
        $entity = $this->getEntity();
        $entity->field = '2.1';

        $this->assertEquals(false, $entity->validation());

        $entity = $this->getEntity();

        $this->assertEquals(true, $entity->validation());
    }

    public function testDigits()
    {
        $entity = $this->getEntity();
        $entity->digits = 2;

        $this->assertEquals(false, $entity->validation());

        $entity = $this->getEntity();
        $entity->digits = 1;

        $this->assertEquals(true, $entity->validation());
    }

    /**
     * @return \TestDecimalModel
     */
    protected function getEntity()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestDecimalModel.php');

        $entity = new \TestDecimalModel();
        $entity->field = '2.12';
        $entity->places = 2;

        return $entity;
    }
}
