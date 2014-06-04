<?php
namespace Phalcon\Tests\Validation\Validator\Upload;

class MimeTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testCanAddMimeTypeValidator()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\MimeType(array(
                'allowedMimeTypes' => array('image/jpeg', 'image/pjpeg', 'image/png'),
            ))
        );
        $this->assertInstanceOf('Phalcon\Validation', $validator);
    }

    public function testMimeTypeValidatorWithCorrectFileGivenShouldNotSetAnyMessages()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\MimeType(array(
                'allowedMimeTypes' => array('image/jpeg'),
            ))
        );
        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getRealType'))
            ->getMock();
        $file->expects($this->once())
            ->method('getRealType')
            ->will($this->returnValue('image/jpeg'));
        $messages = $validator->validate(array(
            'file' => $file,
        ));
        $this->assertCount(0, $messages);
    }

    public function testMimeTypeValidatorWithIncorrectFileGivenShouldSetMessages()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\MimeType(array(
                'allowedMimeTypes' => array('image/jpeg'),
                'message' => 'Invalid mime type ":mimeType". Only files of type jpg is allowed.'
            ))
        );
        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getRealType'))
            ->getMock();
        $file->expects($this->exactly(2))
            ->method('getRealType')
            ->will($this->returnValue('image/png'));
        $messages = $validator->validate(array(
            'file' => $file,
        ));
        $this->assertCount(1, $messages);
        $this->assertEquals('Invalid mime type "image/png". Only files of type jpg is allowed.', $messages[0]->getMessage());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Option "file" must be instance of Phalcon\Http\Request\File.
     */
    public function testMimeTypeValidatorWithInvalidFileOptionFileShouldThrowException()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\MimeType(array(
                'allowedMimeTypes' => array('image/jpeg'),
            ))
        );

        $messages = $validator->validate(array(
            'file' => 'example.txt',
        ));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage "AllowedMimeTypes" option must be array with at least one value set.
     */
    public function testMimeTypeValidatorWithNoAllowedMimeTypesSetShouldThrowException()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\MimeType(array(
                'allowedMimeTypes' => array(),
            ))
        );
        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getRealType'))
            ->getMock();

        $messages = $validator->validate(array(
            'file' => $file,
        ));
        $this->assertCount(0, $messages);
    }
}
