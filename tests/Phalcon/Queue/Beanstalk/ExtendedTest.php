<?php
namespace Phalcon\Tests\Queue\Beanstalk;

use Phalcon\Queue\Beanstalk\Extended;
use Phalcon\Queue\Beanstalk\Job;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * \Phalcon\Tests\Queue\Beanstalk\ExtendedTest
 * Tests for Phalcon\Queue\Beanstalk\Extended component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Nikita Vershinin <endeveit@gmail.com>
 * @package   Phalcon\Queue\Beanstalk\Extended
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class ExtendedTest extends TestCase
{
    /**
     * @var \Phalcon\Queue\Beanstalk\Extended
     */
    protected $client = null;

    protected $tubeName = 'test-tube';

    protected static $shmLimit = 32;

    protected static $shmKey = 0;

    protected function setUp()
    {
        $host = getenv('TEST_BS_HOST');
        $port = getenv('TEST_BS_PORT');

        if (empty($host) || empty($port)) {
            $this->markTestSkipped('TEST_BS_HOST and/or TEST_BS_PORT env variables are not defined');
        }

        $this->client = new Extended([
            'host'   => $host,
            'port'   => $port,
            'prefix' => 'PHPUnit_',
        ]);

        if (!$this->client->connect()) {
            $this->markTestSkipped(sprintf(
                'Need a running beanstalkd server at %s:%d',
                $host,
                $port
            ));
        }

        self::$shmKey = round(microtime(true) * 1000);
    }

    public function testPutAndReserve()
    {
        $this->client->putInTube($this->tubeName, 'testPutInTube');
        $job = $this->client->reserveFromTube($this->tubeName);

        $this->assertNotEmpty($job);
        $this->assertInstanceOf('Phalcon\Queue\Beanstalk\Job', $job);
        $this->assertTrue($job->delete());
    }

    /**
     * @depends testPutAndReserve
     */
    public function testGetTubes()
    {
        $tubes = $this->client->getTubes();

        $this->assertNotEmpty($tubes);
        $this->assertContains($this->tubeName, $tubes);
    }

    /**
     * @depends testGetTubes
     */
    public function testDoWork()
    {
        $expected = [
            'test-tube-1' => '1',
            'test-tube-2' => '2',
        ];

        foreach ($expected as $tube => $value) {
            $this->client->addWorker($tube, function (Job $job) {
                // Store string «test-tube-%JOB_BODY%» in shared memory
                $memory  = shmop_open(self::$shmKey, 'c', 0644, self::$shmLimit);
                $output  = trim(shmop_read($memory, 0, self::$shmLimit));
                $output .= sprintf("\ntest-tube-%s", $job->getBody());

                shmop_write($memory, $output, 0);
                shmop_close($memory);

                exit(1);
            });

            $this->client->putInTube($tube, $value);
        }

        $this->client->doWork();

        $memory = shmop_open(self::$shmKey, 'a', 0, 0);
        $output = shmop_read($memory, 0, self::$shmLimit);

        $this->assertTrue(shmop_delete($memory));
        shmop_close($memory);

        $this->assertNotEmpty($output);

        // Compare number of items in expected list with lines in shared memory
        $this->assertEquals(
            count($expected),
            count(array_unique(explode("\n", trim($output))))
        );
    }
}
