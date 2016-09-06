<?php

/*
 +------------------------------------------------------------------------+
 | Phalcon Framework                                                      |
 +------------------------------------------------------------------------+
 | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
 +------------------------------------------------------------------------+
 | This source file is subject to the New BSD License that is bundled     |
 | with this package in the file docs/LICENSE.txt.                        |
 |                                                                        |
 | If you did not receive a copy of the license and are unable to         |
 | obtain it through the world-wide-web, please send an email             |
 | to license@phalconphp.com so we can send you a copy immediately.       |
 +------------------------------------------------------------------------+
 | Authors: Nikita Vershinin <endeveit@gmail.com>                         |
 +------------------------------------------------------------------------+
 */

namespace Phalcon\Queue\Beanstalk;

use duncan3dc\Helpers\Fork;
use Phalcon\Logger\Adapter as LoggerAdapter;
use Phalcon\Queue\Beanstalk as Base;

/**
 * \Phalcon\Queue\Beanstalk\Extended
 *
 * Extended class to access the beanstalk queue service.
 * Supports tubes prefixes, pcntl-workers and tubes stats.
 *
 * @package Phalcon\Queue\Beanstalk
 */
class Extended extends Base
{
    /**
     * Seconds to wait before putting the job in the ready queue.
     * The job will be in the "delayed" state during this time.
     *
     * @const integer
     */
    const DEFAULT_DELAY = 0;

    /**
     * Jobs with smaller priority values will be scheduled before jobs with larger priorities.
     * The most urgent priority is 0, the least urgent priority is 4294967295.
     *
     * @const integer
     */
    const DEFAULT_PRIORITY = 1024;

    /**
     * Time to run - number of seconds to allow a worker to run this job.
     * The minimum ttr is 1.
     *
     * @const integer
     */
    const DEFAULT_TTR = 60;

    /**
     * If provided the errors will be logged here.
     *
     * @var \Phalcon\Logger\Adapter
     */
    protected $logger = null;

    /**
     * Tubes prefix.
     *
     * @var string
     */
    protected $tubePrefix = null;

    /**
     * Queue handlers.
     *
     * @var array
     */
    protected $workers = [];

    /**
     * {@inheritdoc}
     *
     * @param array $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        $logger = null;
        $tubePrefix = '';

        if (is_array($options) || ($options instanceof \ArrayAccess)) {
            if (isset($options['prefix'])) {
                $tubePrefix = $options['prefix'];
            }

            if (isset($options['logger']) && ($options['logger'] instanceof LoggerAdapter)) {
                $logger = $options['logger'];
            }
        }

        $this->logger = $logger;
        $this->tubePrefix = $tubePrefix;
    }

    /**
     * Adds new worker to the pool.
     *
     * @param  string                    $tube
     * @param  callable                  $callback
     * @throws \InvalidArgumentException
     */
    public function addWorker($tube, $callback)
    {
        if (!is_string($tube)) {
            throw new \InvalidArgumentException('The tube name must be a string.');
        }

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('The callback is invalid.');
        }

        $this->workers[$tube] = $callback;
    }

    /**
     * Runs the main worker cycle.
     *
     * @param boolean $ignoreErrors
     */
    public function doWork($ignoreErrors = false)
    {
        declare (ticks = 1);
        set_time_limit(0);

        $fork = new Fork();
        $fork->ignoreErrors = $ignoreErrors;

        foreach ($this->workers as $tube => $worker) {
            $that = clone $this;

            // Run the worker in separate process.
            $fork->call(function () use ($tube, $worker, $that, $fork, $ignoreErrors) {
                $that->connect();

                do {
                    $job = $that->reserveFromTube($tube);

                    if ($job && ($job instanceof Job)) {
                        $fork->call(function () use ($worker, $job) {
                            call_user_func($worker, $job);
                        });

                        try {
                            $fork->wait();

                            try {
                                $job->delete();
                            } catch (\Exception $e) {
                                if (null !== $this->logger) {
                                    $this->logger->warning(sprintf(
                                        'Exception thrown while deleting the job: %d — %s',
                                        $e->getCode(),
                                        $e->getMessage()
                                    ));
                                }
                            }
                        } catch (\Exception $e) {
                            if (null !== $this->logger) {
                                $this->logger->warning(sprintf(
                                    'Exception thrown while handling job #%s: %d — %s',
                                    $job->getId(),
                                    $e->getCode(),
                                    $e->getMessage()
                                ));
                            }

                            if (!$ignoreErrors) {
                                return;
                            }
                        }
                    } else {
                        // There is no jobs so let's sleep to not increase CPU usage
                        usleep(rand(7000, 10000));
                    }
                } while (true);

                exit(0);
            });
        }

        $fork->wait();
    }

    /**
     * Puts a job on the queue using specified tube.
     *
     * @param string $tube
     * @param string $data
     * @param array  $options
     * @return boolean|string job id or false
     */
    public function putInTube($tube, $data, $options = null)
    {
        if (null === $options) {
            $options = [];
        }

        if (!array_key_exists('delay', $options)) {
            $options['delay'] = self::DEFAULT_DELAY;
        }

        if (!array_key_exists('priority', $options)) {
            $options['priority'] = self::DEFAULT_PRIORITY;
        }

        if (!array_key_exists('ttr', $options)) {
            $options['ttr'] = self::DEFAULT_TTR;
        }

        $this->choose($this->getTubeName($tube));

        return parent::put($data, $options);
    }

    /**
     * Reserves/locks a ready job from the specified tube.
     *
     * @param  string                               $tube
     * @param  integer                              $timeout
     * @return boolean|\Phalcon\Queue\Beanstalk\Job
     */
    public function reserveFromTube($tube, $timeout = null)
    {
        $this->watch($this->getTubeName($tube));

        return parent::reserve($timeout);
    }

    /**
     * Returns the names of all tubes on the server.
     *
     * @return array
     */
    public function getTubes()
    {
        $result = [];
        $lines = $this->getResponseLines('list-tubes');

        if (null !== $lines) {
            foreach ($lines as $line) {
                $line = ltrim($line, '- ');
                if (empty($this->tubePrefix) || (0 === strpos($line, $this->tubePrefix))) {
                    $result[] = !empty($this->tubePrefix) ? substr($line, strlen($this->tubePrefix)) : $line;
                }
            }
        }

        return $result;
    }

    /**
     * Returns information about the specified tube if it exists.
     *
     * @param  string     $tube
     * @return null|array
     */
    public function getTubeStats($tube)
    {
        $result = null;
        $lines = $this->getResponseLines('stats-tube ' . $this->getTubeName($tube));

        if (!empty($lines)) {
            foreach ($lines as $line) {
                if (false !== strpos($line, ':')) {
                    list($name, $value) = explode(':', $line);
                    if (null !== $value) {
                        $result[$name] = intval($value);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns information about specified job if it exists
     *
     * @param  int        $job_id
     * @return null|array
     */
    public function getJobStats($job_id)
    {
        $result = null;
        $lines = $this->getResponseLines('stats-job ' . (int) $job_id);

        if (!empty($lines)) {
            foreach ($lines as $line) {
                if (false !== strpos($line, ':')) {
                    list($name, $value) = explode(':', $line);
                    if (null !== $value) {
                        $result[$name] = intval($value);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns the number of tube watched by current session.
     * Example return array: ['WATCHED' => 1]
     * Added on 10-Jan-2014 20:04 IST by Tapan Kumar Thapa @ tapan.thapa@yahoo.com
     *
     * @param  string     $tube
     * @return null|array
     */
    public function ignoreTube($tube)
    {
        $result = null;
        $lines = $this->getWatchingResponse('ignore ' . $this->getTubeName($tube));

        if (!empty($lines)) {
            list($name, $value) = explode(' ', $lines);
            if (null !== $value) {
                $result[$name] = intval($value);
            }
        }

        return $result;
    }

    /**
     * Returns the tube name with prefix.
     *
     * @param  string|null $tube
     * @return string
     */
    protected function getTubeName($tube)
    {
        if ((null !== $this->tubePrefix) && (null !== $tube)) {
            $tube = str_replace($this->tubePrefix, '', $tube);

            if (0 !== strcmp($tube, 'default')) {
                return $this->tubePrefix . $tube;
            }
        }

        return $tube;
    }

    /**
     * Returns the result of command that wait the list in response from beanstalkd.
     *
     * @param  string            $cmd
     * @return array|null
     * @throws \RuntimeException
     */
    protected function getResponseLines($cmd)
    {
        $result = null;
        $this->write(trim($cmd));

        $response = $this->read();
        $matches = [];

        if (!preg_match('#^(OK (\d+))#mi', $response, $matches)) {
            throw new \RuntimeException(sprintf(
                'Unhandled response: %s',
                $response
            ));
        }

        $result = preg_split("#[\r\n]+#", rtrim($this->read($matches[2])));

        // discard header line
        if (isset($result[0]) && $result[0] == '---') {
            array_shift($result);
        }

        return $result;
    }

    /**
     * Returns the result of command that wait the list in response from beanstalkd.
     * Added on 10-Jan-2014 20:04 IST by Tapan Kumar Thapa @ tapan.thapa@yahoo.com
     *
     * @param  string            $cmd
     * @return string|null
     * @throws \RuntimeException
     */
    protected function getWatchingResponse($cmd)
    {
        $result = null;
        $nbBytes = $this->write($cmd);

        if ($nbBytes && ($nbBytes > 0)) {
            $response = $this->read($nbBytes);
            $matches = [];

            if (!preg_match('#^WATCHING (\d+).*?#', $response, $matches)) {
                throw new \RuntimeException(sprintf(
                    'Unhandled response: %s',
                    $response
                ));
            }

            $result = $response;
        }

        return $result;
    }
}
