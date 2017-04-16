<?php
namespace Phalcon\Tests\Filter\File;

class Mime2ExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateFilter()
    {
        $filter = new \Phalcon\Filter\File\Mime2Extension();
        $this->assertInstanceOf('Phalcon\Filter\File\Mime2Extension', $filter);
    }

    public function testFilteringPngMimeTypeShouldReturnPngExtension()
    {
        $filter = new \Phalcon\Filter\File\Mime2Extension();
        $this->assertEquals('png', $filter->filter('image/png'));
    }

    /**
     * @expectedException \Phalcon\Filter\File\Exception
     * @expectedExceptionMessage Could not filter mime type "non/existent" to appropriate extension.
     */
    public function testFilteringNonExistentMimeTypeExtensionShouldThrowException()
    {
        $filter = new \Phalcon\Filter\File\Mime2Extension();
        $filter->filter('non/existent');
    }

    public function testUsingFilterWithPhalconFilterShouldNotCauseErrors()
    {
        $filter = new \Phalcon\Filter();
        $filter->add('mime2extension', new \Phalcon\Filter\File\Mime2Extension());
        $this->assertEquals('png', $filter->sanitize('image/png', 'mime2extension'));
    }
} 