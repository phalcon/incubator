<?php
namespace Phalcon\Tests\Tag;

class Id2DirTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultUsageOfId2DirShouldReturnPath()
    {
        $this->assertEquals('000/001/234/56', \Phalcon\Tag\Id2Dir::id2Dir(123456));
    }
} 