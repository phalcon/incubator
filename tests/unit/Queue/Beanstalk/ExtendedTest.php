<?php

namespace Phalcon\Tests\Queue\Beanstalk;

use UnitTester;
use Codeception\TestCase\Test;
use Phalcon\Queue\Beanstalk\Job;
use Phalcon\Queue\Beanstalk\Extended;

/**
 * \Phalcon\Tests\Queue\Beanstalk\ExtendedTest
 * Tests for Phalcon\Queue\Beanstalk\Extended component
 *
 * @copyright (c) 2011-2017 Phalcon Team
 * @link      https://phalconphp.com
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

    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var Extended
     */
    protected $client = null;

    protected $shmLimit = 32;

    protected $shmKey = 0;

    /**
     * executed before each test
     */
    protected function _before()
    {
        $this->client = new Extended([
            'host'   => env('TEST_BT_HOST', 6379),
            'port'   => env('TEST_BT_PORT', 11300),
            'prefix' => 'PHPUnit\\',
        ]);

        if (!$this->client->connect()) {
            $this->markTestSkipped(sprintf(
                'Need a running beanstalkd server at %s:%d',
                env('TEST_BT_HOST', 6379),
                env('TEST_BT_PORT', 11300)
            ));
        }

        $this->shmKey = round(microtime(true) * 1000);
    }

    public function testShouldPutAndReserve()
    {
        $jobId = $this->client->putInTube(self::TUBE_NAME, 'testPutInTube');

        $this->assertNotEquals(false, $jobId);

        $job = $this->client->reserveFromTube(self::TUBE_NAME);

        $this->assertNotEmpty($job);
        $this->assertInstanceOf(Job::class, $job);
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
        if (!class_exists('\duncan3dc\Helpers\Fork') && !class_exists('\duncan3dc\Forker\Fork')) {
            $this->markTestSkipped(sprintf(
                '%s uses fork-helper as a dependency. You can install it by running: ' .
                'composer require duncan3dc/fork-helper',
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

        // Check if we are using Fork1.0 (php < 7)
        if (class_exists('duncan3dc\Helpers\Fork')) {
            $fork = new \duncan3dc\Helpers\Fork;
        } else {
            $fork = new \duncan3dc\Forker\Fork;
        }

        $that = $this;

        $fork->call(function () use ($expected, $that) {
            foreach ($expected as $tube => $value) {
                $that->client->addWorker($tube, function (Job $job) {
                    // Store string "test-tube-%JOB_BODY%" in a shared memory
                    $memory  = shmop_open($this->shmKey, 'c', 0644, $this->shmLimit);
                    $output  = trim(shmop_read($memory, 0, $this->shmLimit));
                    $output .= sprintf("\ntest-tube-%s", $job->getBody());

                    shmop_write($memory, $output, 0);
                    shmop_close($memory);

                    throw new \RuntimeException('Forced exception to stop worker');
                });

                $that->assertNotEquals(false, $that->client->putInTube($tube, $value));
            }

            $that->client->doWork();

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

        $this->assertEquals(
            count($expected),
            count($actual),
            sprintf(
                "Compare number of items in expected list with lines in shared memory failed.\nExpected: %s\nActual: %s\n",
                json_encode($expected, JSON_PRETTY_PRINT),
                json_encode($actual, JSON_PRETTY_PRINT)
            )
        );

        foreach ($actual as $value) {
            $this->assertArrayHasKey($value, $expected);
        }
    }
}
