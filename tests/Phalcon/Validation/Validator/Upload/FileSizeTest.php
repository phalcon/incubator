<?php
namespace Phalcon\Tests\Validation\Validator\Upload;

class FileSizeTest extends \PHPUnit_Framework_TestCase
{
    public function testCanAddFileSizeValidator()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
                'min' => 1024,
                'max' => 2048,
            ))
        );
        $this->assertInstanceOf('Phalcon\Validation', $validator);
    }

    public function testValidatingUploadedFileWithCorrectSizeShouldNotSetAnyMessages()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
                'min' => 1024,
                'max' => 2048,
            ))
        );
        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getSize'))
            ->getMock();
        $file->expects($this->exactly(2))
            ->method('getSize')
            ->will($this->returnValue(1024));
        $messages = $validator->validate(array(
            'file' => $file,
        ));
        $this->assertCount(0, $messages);
    }

    public function testValidatingUploadedFileWithLessThenMinSizeShouldSetValidationMessage()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
                'min' => 1024,
            ))
        );
        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getSize'))
            ->getMock();
        $file->expects($this->exactly(2))
            ->method('getSize')
            ->will($this->returnValue(1023));
        $messages = $validator->validate(array(
            'file' => $file,
        ));
        $this->assertCount(1, $messages);
    }

    public function testValidatingUploadedFileWithMoreThenMaxSizeShouldSetValidationMessage()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
                'max' => 2048,
            ))
        );
        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getSize'))
            ->getMock();
        $file->expects($this->exactly(2))
            ->method('getSize')
            ->will($this->returnValue(2049));
        $messages = $validator->validate(array(
            'file' => $file,
        ));
        $this->assertCount(1, $messages);
    }

    public function testValidatingUploadedFileWithLessThenMinSizeAndMessageSetShouldSetGivenValidationMessage()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
                'min' => 1024,
                'message' => 'Field ":field" cannot be less then 1024 bytes in size. File size is :fileSize bytes.',
            ))
        );
        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getSize'))
            ->getMock();
        $file->expects($this->exactly(2))
            ->method('getSize')
            ->will($this->returnValue(1023));
        $messages = $validator->validate(array(
            'file' => $file,
        ));
        $this->assertCount(1, $messages);
        $this->assertEquals(
            'Field "file" cannot be less then 1024 bytes in size. File size is 1023 bytes.',
            $messages[0]->getMessage()
        );
    }

    public function testValidatingUploadedFileWithLessThenMinSizeAndMinMessageSetShouldSetGivenValidationMessage()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
                'min' => 1024,
                'minMessage' => 'Field ":field" cannot be less then 1024 bytes in size. File size is :fileSize bytes.',
            ))
        );
        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getSize'))
            ->getMock();
        $file->expects($this->exactly(2))
            ->method('getSize')
            ->will($this->returnValue(1023));
        $messages = $validator->validate(array(
            'file' => $file,
        ));
        $this->assertCount(1, $messages);
        $this->assertEquals(
            'Field "file" cannot be less then 1024 bytes in size. File size is 1023 bytes.',
            $messages[0]->getMessage()
        );
    }

    public function testValidatingUploadedFileWithMoreThenMaxSizeAndMaxMessageSetShouldSetGivenValidationMessage()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
                'max' => 2048,
                'maxMessage' => 'Field ":field" cannot be more then 2048 bytes in size. File size is :fileSize.',
            ))
        );
        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getSize'))
            ->getMock();
        $file->expects($this->exactly(2))
            ->method('getSize')
            ->will($this->returnValue(2049));
        $messages = $validator->validate(array(
            'file' => $file,
        ));
        $this->assertCount(1, $messages);
        $this->assertEquals(
            'Field "file" cannot be more then 2048 bytes in size. File size is 2049.',
            $messages[0]->getMessage()
        );
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage At least one of "min" or "max" options must be set.
     */
    public function testOmittingOptionsShouldThrowException()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
            ))
        );
        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getSize'))
            ->getMock();

        $messages = $validator->validate(array(
            'file' => $file,
        ));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Option "file" must be instance of Phalcon\Http\Request\File.
     */
    public function testFieldWhichIsNotUploadedFileShouldThrowException()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
            ))
        );

        $messages = $validator->validate(array(
            'file' => 'filename.txt',
        ));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Option "min" cannot be zero or negative number. -100 given.
     */
    public function testSettingNegativeMinOptionValueShouldThrowException()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
                'min' => -100,
            ))
        );

        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getSize'))
            ->getMock();

        $messages = $validator->validate(array(
            'file' => $file,
        ));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Option "max" cannot be zero or negative number. -100 given.
     */
    public function testSettingNegativeMaxOptionValueShouldThrowException()
    {
        $validator = new \Phalcon\Validation();
        $validator->add(
            'file',
            new \Phalcon\Validation\Validator\Upload\FileSize(array(
                'max' => -100,
            ))
        );

        $file = $this->getMockBuilder('Phalcon\Http\Request\File')
            ->disableOriginalConstructor()
            ->setMethods(array('getSize'))
            ->getMock();

        $messages = $validator->validate(array(
            'file' => $file,
        ));
    }
} 