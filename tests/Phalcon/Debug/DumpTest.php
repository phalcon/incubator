<?php
namespace Phalcon\Tests\Debug;
class DumpTest extends \PHPUnit_Framework_TestCase
{
    
    public function testDumpingVarByDefaultShouldEchoIt()
    {
        $var = array(1,2,3);
        ob_start();
        \Phalcon\Tests\Stubs\Debug\Dump::dump($var);
        $output = ob_get_clean();
        // assert backtrace exists
        $this->assertContains('Line: 10', $output);
        $this->assertContains('DumpTest.php', $output);
        // assert array was outputted
        $expected = <<<HEREDOC
array(3) {
  [0] =>
  int(1)
  [1] =>
  int(2)
  [2] =>
  int(3)
}
HEREDOC;
        $this->assertContains($expected, $output);
    }
}
