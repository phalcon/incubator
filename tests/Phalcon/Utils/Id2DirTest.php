<?php
namespace Phalcon\Tests\Utils;

class Id2DirTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultUsageOfId2DirShouldReturnPath()
    {
        $this->assertEquals('000/001/234/56', \Phalcon\Utils\Id2Dir::id2Dir(123456));
    }
} 