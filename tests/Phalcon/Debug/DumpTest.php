<?php
namespace Phalcon\Tests\Debug;
class DumpTest extends \PHPUnit_Framework_TestCase
{
    protected $fixtures = array
    (
        'htmlString' => '<p>test</p>',
    );
    
    // set for testing purposes
    protected $flushBuffer = false;
    
    public function testDumpingHtmlStringVarByDefaultShouldEchoNonEscapedDump()
    {
        ob_start();
        $dump = new \Phalcon\Debug\Dump($this->flushBuffer);
        $dump->dump($this->fixtures['htmlString']);
        $output = ob_get_clean();
        $this->assertBacktraceExists($output);
        // assert string was not converted
        $this->assertContains($this->fixtures['htmlString'], $output);
        // assert correct sapi set
        $this->assertEquals(PHP_SAPI, \Phalcon\Debug\Dump::getSapi());
    }
    
    public function testDumpingHtmlStringVarWithNonCliSapiShouldEchoEscapedDump()
    {
        // non cli sapi
        \Phalcon\Debug\Dump::setSapi('apache');
        
        ob_start();
        $dump = new \Phalcon\Debug\Dump($this->flushBuffer);
        $dump->dump($this->fixtures['htmlString']);
        $output = ob_get_clean();
        $this->assertBacktraceExists($output);
        // assert string was converted
        $this->assertContains(htmlentities($this->fixtures['htmlString'], ENT_QUOTES, 'UTF-8'), $output);
        // assert correct sapi set
        $this->assertEquals('apache', \Phalcon\Debug\Dump::getSapi());
    }
    
    public function testDumpingHtmlStringVarWithSupressedOutputShouldReturnValue()
    {
        // supress output
        \Phalcon\Debug\Dump::setOutput(false);
        
        ob_start();
        $dump = new \Phalcon\Debug\Dump($this->flushBuffer);
        $dump->dump($this->fixtures['htmlString']);
        $output = ob_get_clean();
        $this->assertBacktraceNotExists($output);
        // assert no output
        $this->assertEmpty($output);
        // assert returned value has backtrace
        $this->assertBacktraceExists($return);
        // assert dump was returned
        $this->assertContains($this->fixtures['htmlString'], $return);
        // assert right output flag was set
        $this->assertFalse(\Phalcon\Debug\Dump::getOutput());
    }
    
    public function testSecondDumpParamShouldOverrideGlobalOutputSetting()
    {
        ob_start();
        $dump = new \Phalcon\Debug\Dump($this->flushBuffer);
        $return = $dump->dump($this->fixtures['htmlString'], false);
        $output = ob_get_clean();
        // no backtrace
        $this->assertBacktraceNotExists($output);
        // assert no output
        $this->assertEmpty($output);
        // assert dump was returned
        $this->assertContains($this->fixtures['htmlString'], $return);
    }
    
    public function testIfNoXdebugVarDumpObjectShouldFallback()
    {
        /* @var $mockDumpStub \PhalconDebug\Dump */
        $mockDumpStub = $this->getMock('\Phalcon\Debug\Dump', array('xdebugDumpExists'), 
            array($this->flushBuffer));
        
        $mockDumpStub->expects($this->once())
            ->method('xdebugDumpExists')
            ->will($this->returnValue(false));
        
        ob_start();
        $mockDumpStub->dump($this->fixtures['htmlString']);
        $output = ob_get_clean(); 
        $this->assertBacktraceExists($output);
        // assert string was not converted
        $this->assertContains($this->fixtures['htmlString'], $output);
        
    }
    
    public function testObGetCleanShouldReturnEmptyIfFlushBufferNotSet()
    {
        ob_start();
        $dump   = new \Phalcon\Debug\Dump();
        $return = $dump->dump($this->fixtures['htmlString']);
        $output = ob_get_clean();
        
        // assert output empty
        $this->assertEmpty($output);
        $this->assertBacktraceExists($return);
        $this->assertContains($this->fixtures['htmlString'], $return);

    }
        
    protected function assertBacktraceExists($output)
    {
        // assert backtrace exists
        $this->assertContains('Line:', $output);
        $this->assertContains('DumpTest.php', $output);
    }
    
    protected function assertBacktraceNotExists($output)
    {
        // assert backtrace doesnt exist
        $this->assertNotContains('Line:', $output);
        $this->assertNotContains('DumpTest.php', $output);
    }
    
    protected function tearDown()
    {
        parent::tearDown();
        // reset output flag to default
        \Phalcon\Debug\Dump::setOutput(true);
        // reset PHP_SAPI
        \Phalcon\Debug\Dump::setSapi(PHP_SAPI);
        
    }
}
