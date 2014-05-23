<?php
namespace Phalcon\Mvc\Model\Behavior;

class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        /**
         * need to instance DI before tests or else Behavior will throw exception that DI container is required in order
         * to obtain ORM services even tho DI is unused (possible bug?)
         */
        new \Phalcon\DI\FactoryDefault();
    }

    public function testBehaviorOnValidationEventWithNoOptions()
    {

        $datetimeBehavior = new \Phalcon\Mvc\Model\Behavior\DateTime(
            array(
                'createdAt' => array()
            )
        );
        $model = new \Phalcon\Mvc\Model\Behavior\DateTimeModelStub();

        $model->addBehavior($datetimeBehavior);
        $datetimeBehavior->notify('validation', $model);
        $this->assertEquals('2014-05-16 15:19:00', $model->getCreatedAt());
    }

    public function testBehaviorOnValidationEventWithTimezoneOptionShouldCreateObject()
    {
        $datetimeBehavior = new \Phalcon\Mvc\Model\Behavior\DateTime(
            array(
                'createdAt' => array(
                    'timezone' => 'Europe/Belgrade',
                )
            )
        );
        $model = new \Phalcon\Mvc\Model\Behavior\DateTimeModelStub();
        $model->addBehavior($datetimeBehavior);
        $datetimeBehavior->notify('validation', $model);
        $this->assertEquals('2014-05-16 15:19:00', $model->getCreatedAt());
        $datetimeBehavior->notify('afterCreate', $model);
        $this->assertEquals('Europe/Belgrade', $model->getCreatedAt()->getTimezone()->getName());
    }

    public function testBehaviorOnValidationAndAfterCreateEventWithNoOptions()
    {
        $datetimeBehavior = new \Phalcon\Mvc\Model\Behavior\DateTime(
            array(
                'createdAt' => array()
            )
        );
        $model = new \Phalcon\Mvc\Model\Behavior\DateTimeModelStub();
        $model->addBehavior($datetimeBehavior);
        $datetimeBehavior->notify('validation', $model);
        $datetimeBehavior->notify('afterCreate', $model);
        $this->assertEquals('2014-05-16 15:19:00', $model->getCreatedAt()->format('Y-m-d H:i:s'));
    }

    public function testBehaviorOnValidationAndAfterCreateEventWithCustomClassNameOptionWillReturnCustomObject()
    {
        $datetimeBehavior = new \Phalcon\Mvc\Model\Behavior\DateTime(
            array(
                'createdAt' => array(
                    'className' => 'Phalcon\Mvc\Model\Behavior\DateTimeStub',
                )
            )
        );
        $model = new \Phalcon\Mvc\Model\Behavior\DateTimeModelStub();
        $model->addBehavior($datetimeBehavior);
        $datetimeBehavior->notify('validation', $model);
        $datetimeBehavior->notify('afterCreate', $model);
        $this->assertInstanceOf('Phalcon\Mvc\Model\Behavior\DateTimeStub', $model->getCreatedAt());
        $this->assertEquals('2014-05-16 15:19:00', $model->getCreatedAt()->format('Y-m-d H:i:s'));
    }

    public function testBehaviorOnValidationEventWithEmptyFieldShouldContinueWithoutDoingAnything()
    {
        $datetimeBehavior = new \Phalcon\Mvc\Model\Behavior\DateTime(
            array(
                'empty' => array()
            )
        );
        $model = new \Phalcon\Mvc\Model\Behavior\DateTimeModelStub();
        $model->addBehavior($datetimeBehavior);
        $datetimeBehavior->notify('validation', $model);
        $this->assertNull($model->getEmpty());
    }

    public function testBehaviorOnNonRequiredEventShouldReturnFalse()
    {
        $datetimeBehavior = new \Phalcon\Mvc\Model\Behavior\DateTime(
            array(
                'createdAt' => array()
            )
        );
        $model = new \Phalcon\Mvc\Model\Behavior\DateTimeModelStub();
        $model->addBehavior($datetimeBehavior);
        $this->assertNull($datetimeBehavior->notify('someInvalidEvent', $model));
    }

    /**
     * @expectedException \Phalcon\Mvc\Model\Behavior\Exception
     * @expectedExceptionMessage Property "timezone" must be instance of DateTime object. "DateTimeZone" given.
     */
    public function testBehaviorOnValidationAndAfterCreateEventWithWrongTypeFieldShouldThrowException()
    {
        $datetimeBehavior = new \Phalcon\Mvc\Model\Behavior\DateTime(
            array(
                'timezone' => array()
            )
        );
        $model = new \Phalcon\Mvc\Model\Behavior\DateTimeModelStub();
        $model->addBehavior($datetimeBehavior);
        $datetimeBehavior->notify('validation', $model);
        $datetimeBehavior->notify('afterCreate', $model);
        $this->assertEquals('2014-05-16 15:19:00', $model->getCreatedAt()->format('Y-m-d H:i:s'));
    }
}

class DateTimeModelStub extends \Phalcon\Mvc\Model
{
    private $createdAt;

    private $timezone;

    private $empty;

    public function onConstruct()
    {
        $this->createdAt = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2014-05-16 15:19:00',
            new \DateTimeZone('Europe/Belgrade')
        );
        $this->timezone = new \DateTimeZone('Europe/Belgrade');
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getEmpty()
    {
        return $this->empty;
    }
}

class DateTimeStub extends \DateTime
{
    /**
     * Overriden CreateFromFormat.
     *
     * @param string $format format
     * @param string $time   time
     * @param null   $object object
     *
     * @return \static
     */
    public static function createFromFormat($format, $time, $object = null)
    {
        $datetime = new static();
        $parentDateTime = parent::createFromFormat($format, $time, $object);
        $datetime->setTimestamp($parentDateTime->getTimestamp());
        $datetime->setTimezone($parentDateTime->getTimezone());
        return $datetime;
    }
}
