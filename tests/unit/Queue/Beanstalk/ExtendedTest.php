<?php
namespace Phalcon\Tests\Queue\Beanstalk;

use Codeception\TestCase\Test;
use Phalcon\Queue\Beanstalk\Extended;
use Phalcon\Queue\Beanstalk\Job;
use UnitTester;

/**
 * \Phalcon\Tests\Queue\Beanstalk\ExtendedTest
 * Tests for Phalcon\Queue\Beanstalk\Extended component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Nikita Vershinin <endeveit@gmail.com>
 * @package   Phalcon\Tests\Queue\Beanstalk
 * @group     Beanstalk
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class ExtendedTest extends Test
{
    const TUBE_NAME = 'test-tube';
    const JOB_CLASS = 'Phalcon\Queue\Beanstalk\Job';

    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var \Phalcon\Queue\Beanstalk\Extended
     */
    protected $client = null;

    protected $shmLimit = 32;

    protected $shmKey = 0;

    /**
     * executed before each test
     */
    protected function _before()
    {
        if (!defined('TEST_BT_HOST') || !defined('TEST_BT_PORT')) {
            $this->markTestSkipped('TEST_BT_HOST and/or TEST_BT_PORT env variables are not defined');
        }

        $this->client = new Extended([
            'host'   => TEST_BT_HOST,
            'port'   => TEST_BT_PORT,
            'prefix' => 'PHPUnit_',
        ]);

        if (!$this->client->connect()) {
            $this->markTestSkipped(sprintf(
                'Need a running beanstalkd server at %s:%d',
                TEST_BT_HOST,
                TEST_BT_PORT
            ));
        }

        $this->shmKey = round(microtime(true) * 1000);
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    public function testShouldPutAndReserve()
    {
        $jobId = $this->client->putInTube(self::TUBE_NAME, 'testPutInTube');

        $this->assertNotEquals(false, $jobId);

        $job = $this->client->reserveFromTube(self::TUBE_NAME);

        $this->assertNotEmpty($job);
        $this->assertInstanceOf(self::JOB_CLASS, $job);
        $this->assertEquals($jobId, $job->getId());
        $this->assertTrue($job->delete());
    }

    /**
     * @depends testShouldPutAndReserve
     */
    public function testShouldGetTubes()
    {
        $tubes = $this->client->getTubes();

        $this->assertNotEmpty($tubes);
        $this->assertContains(self::TUBE_NAME, $tubes);

        // Cleanup tubes
        foreach ($tubes as $tube) {
            $isRunning = true;

            $this->client->watch($tube);

            do {
                $job = $this->client->reserve(0.1);

                if ($job) {
                    $this->assertTrue($job->delete());
                } else {
                    $isRunning = false;
                }
            } while ($isRunning);
        }
    }

    /**
     * @depends testShouldGetTubes
     */
    public function testShouldDoWork()
    {
        if (!class_exists('\duncan3dc\Helpers\Fork')) {
            $this->markTestSkipped(sprintf(
                '%s used as a dependency \duncan3dc\Helpers\Fork. You can install it by using' .
                'composer require "duncan3dc/fork-helper":"*"',
                get_class($this->client)
            ));
        }

        $memory = shmop_open($this->shmKey, 'c', 0644, $this->shmLimit);

        if (false === $memory) {
            $this->markTestSkipped('Cannot create shared memory block');
        } else {
            shmop_close($memory);
        }

        $expected = [
            'test-tube-1' => '1',
            'test-tube-2' => '2',
        ];

        $fork = new \duncan3dc\Helpers\Fork();
        $fork->call(function () use ($expected) {
            foreach ($expected as $tube => $value) {
                $this->client->addWorker($tube, function (Job $job) {
                    // Store string "test-tube-%JOB_BODY%" in a shared memory
                    $memory  = shmop_open($this->shmKey, 'c', 0644, $this->shmLimit);
                    $output  = trim(shmop_read($memory, 0, $this->shmLimit));
                    $output .= sprintf("\ntest-tube-%s", $job->getBody());

                    shmop_write($memory, $output, 0);
                    shmop_close($memory);

                    throw new \RuntimeException('Forced exception to stop worker');
                });

                $this->assertNotEquals(false, $this->client->putInTube($tube, $value));
            }

            $this->client->doWork();

            exit(0);
        });

        $reflectionFork    = new \ReflectionClass($fork);
        $reflectionThreads = $reflectionFork->getProperty('threads');
        $reflectionThreads->setAccessible(true);

        sleep(2);

        $reflectionThreads->setValue($fork, []);
        unset($fork);

        $memory = shmop_open($this->shmKey, 'a', 0, 0);
        $output = shmop_read($memory, 0, $this->shmLimit);

        $this->assertTrue(shmop_delete($memory));
        shmop_close($memory);

        $this->assertNotEmpty($output);

        $actual = explode("\n", trim($output));

        // Compare number of items in expected list with lines in shared memory
        $this->assertEquals(count($expected), count($actual));

        foreach ($actual as $value) {
            $this->assertArrayHasKey($value, $expected);
        }
    }
}
