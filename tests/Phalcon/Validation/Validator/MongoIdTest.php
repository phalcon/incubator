<?php
namespace Phalcon\Test\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Validator\MongoId;
/**
 * MongoId validator test
 *
 * @author Kachit
 */
class MongoIdTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MongoId
     */
    private $testable;

    /**
     * @var Validation
     */
    private $validation;

    protected function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped(
                'The Mongo extension is not available.'
            );
        }
        $this->testable = new MongoId();
        $this->validation = new Validation();
    }

    public function testInvalidValue()
    {
        $array = ['id' => 123];
        $this->validation->add('id', $this->testable);
        $messages = $this->validation->validate($array);
        $this->assertEquals(1, count($messages));
        $this->assertEquals('MongoId is not valid', $messages[0]->getMessage());
        $this->assertEquals('MongoId', $messages[0]->getType());
    }

    public function testValidValue()
    {
        $array = ['id' => '561824e063e702bc1900002a'];
        $this->validation->add('id', $this->testable);
        $messages = $this->validation->validate($array);
        $this->assertEquals(0, count($messages));
    }

    public function testEmptyValue()
    {
        $array = ['id' => ''];
        $this->validation->add('id', $this->testable);
        $messages = $this->validation->validate($array);
        $this->assertEquals(1, count($messages));
        $this->assertEquals('MongoId is not valid', $messages[0]->getMessage());
        $this->assertEquals('MongoId', $messages[0]->getType());
    }

    public function testEmptyValueWithAllowEmptyOption()
    {
        $array = ['id' => ''];
        $this->testable->setOption('allowEmpty', true);
        $this->validation->add('id', $this->testable);
        $messages = $this->validation->validate($array);
        $this->assertEquals(0, count($messages));
    }
}
