<?php
/**
 * Phalcon Framework
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phalconphp.com so we can send you a copy immediately.
 *
 * @author Nikita Vershinin <endeveit@gmail.com>
 */
namespace Phalcon\Queue\Beanstalk;

use Phalcon\Logger\Adapter as LoggerAdapter;
use Phalcon\Queue\Beanstalk as Base;
use Phalcon\Queue\Beanstalk\Job;

/**
 * \Phalcon\Queue\Beanstalk\Extended
 * Extended class to access the beanstalk queue service.
 * Supports tubes prefixes, pcntl-workers and tubes stats.
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
    protected $workers = array();

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
     * @throws \RuntimeException
     */
    public function doWork()
    {
        if (!extension_loaded('pcntl')) {
            throw new \RuntimeException('The pcntl extension is required for workers');
        }

        declare (ticks = 1);
        set_time_limit(0);

        $tubes = array_keys($this->workers);

        do {
            if (!empty($this->workers)) {
                $tube = $tubes[array_rand($tubes)];
                $job = $this->reserveFromTube($tube);

                if ($job && ($job instanceof Job)) {
                    $this->spawn($this->workers[$tube], $job);
                } else {
                    // There is no jobs so let's sleep to not increase CPU usage
                    usleep(rand(7000, 10000));
                }
            } else {
                sleep(10);
            }
        } while (true);
    }

    /**
     * Puts a job on the queue using specified tube.
     *
     * @param string $tube
     * @param string $data
     * @param array  $options
     */
    public function putInTube($tube, $data, $options = null)
    {
        if (null === $options) {
            $options = array();
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

        parent::put($data, $options);
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
        $result = array();
        $lines = $this->getResponseLines('list-tubes');

        if (null !== $lines) {
            foreach ($lines as $line) {
                $line = ltrim($line, '- ');
                if (empty($this->tubePrefix) || (0 === strpos($line, $this->tubePrefix))) {
                    $result[] = $line;
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
        $lines = $this->getResponseLines('stats-job ' . (int)$job_id);

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
     * Returns the number of tube watched by current session.
     * Example return array: array('WATCHED' => 1)
     * Added on 10-Jan-2014 20:04 IST by Tapan Kumar Thapa @ tapan.thapa@yahoo.com
     *
     * @param  string     $tube
     * @return null|array
    */
    public function ignoreTube($tube)
    {
        $result = null;
        $lines  = $this->getResponseLinesText('ignore ' . $this->getTubeName($tube));

        if (null !== $lines) {
            list($name, $value) = explode(' ', $lines);
            if (null !== $value) {
                $result[$name] = intval($value);
            }
        }

        return $result;
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
        $matches  = array();

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
        $result  = null;
        $nbBytes = $this->write($cmd);

        if ($nbBytes && ($nbBytes > 0)) {
            $response = $this->read($nbBytes);
            $matches  = array();

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

    /**
     * Runs the worker in separate process.
     *
     * @param  callable                     $callable
     * @param  \Phalcon\Queue\Beanstalk\Job $job
     * @return boolean
     * @throws \RuntimeException
     */
    private function spawn($callable, Job $job)
    {
        $pid = pcntl_fork();

        switch ($pid) {
            case -1:
                throw new \RuntimeException('Fork failed, bailing');
                break;
            case 0:
                // We're in the child process
                call_user_func($callable, $job);
                break;
            default:
                // Wait for success exit code — exit(0)
                pcntl_waitpid($pid, $status);
                $result = pcntl_wexitstatus($status);

                if ($result != 0) {
                    // Something goes wrong
                    return false;
                } else {
                    // If everything is OK, delete the job from queue
                    try {
                        $job->delete();
                    } catch (\Exception $e) {
                        if (null !== $this->logger) {
                            $this->logger->warning(sprintf(
                                'Exception thrown when trying to delete job: %d — %s',
                                $e->getCode(),
                                $e->getMessage()
                            ));
                        }
                    }
                }

                break;
        }

        return true;
    }
}
