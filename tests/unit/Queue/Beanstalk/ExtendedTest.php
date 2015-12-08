<?php

namespace Phalcon\Tests\Queue\Beanstalk;

use Phalcon\Queue\Beanstalk\Extended;
use Phalcon\Queue\Beanstalk\Job;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Tests\Queue\Beanstalk\ExtendedTest
 * Tests for Phalcon\Queue\Beanstalk\Extended component
 *
 * @copyright (c) 2011-2015 Phalcon Team
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
        $this->client->putInTube(self::TUBE_NAME, 'testPutInTube');
        $job = $this->client->reserveFromTube(self::TUBE_NAME);

        $this->assertNotEmpty($job);
        $this->assertInstanceOf(self::JOB_CLASS, $job);
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
    }

    /**
     * @depends testShouldGetTubes
     */
    public function testShouldDoWork()
    {
        $this->markTestSkipped('This test stops forever and probably it is broken');

        if (!class_exists('\duncan3dc\Helpers\Fork')) {
            $this->markTestSkipped(sprintf(
                '%s used as a dependency \duncan3dc\Helpers\Fork. You can install it by using' .
                'composer require "duncan3dc/fork-helper":"*"',
                get_class($this->client)
            ));
        }

        $expected = [
            'test-tube-1' => '1',
            'test-tube-2' => '2',
        ];

        foreach ($expected as $tube => $value) {
            $this->client->addWorker($tube, function (Job $job) {
                // Store string "test-tube-%JOB_BODY%" in shared memory
                $memory  = shmop_open($this->shmKey, 'c', 0644, $this->shmLimit);
                $output  = trim(shmop_read($memory, 0, $this->shmLimit));
                $output .= sprintf("\ntest-tube-%s", $job->getBody());

                shmop_write($memory, $output, 0);
                shmop_close($memory);

                exit(1);
            });

            $this->client->putInTube($tube, $value);
        }

        $this->client->doWork();

        $memory = shmop_open($this->shmKey, 'a', 0, 0);
        $output = shmop_read($memory, 0, $this->shmLimit);

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
