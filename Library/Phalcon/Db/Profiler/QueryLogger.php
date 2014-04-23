<?php
namespace Phalcon\Db\Profiler;

/**
 * Query logging and profiling component.
 * Meant to be used for debugging and/or optimization purposes in combination with Phalcon's DB events.
 * By default, logs query information (sql, vars to be binded, query exection time,
 * bind vars types and db host which executes query) using Firephp adapter with priority set to \Phalcon\Logger::DEBUG.
 *
 * <code>
 * $eventsManager = new \Phalcon\Events\Manager();
 * $queryLogger = new \Phalcon\Db\Profiler\QueryLogger();
 * $eventsManager->attach('db', $queryLogger);
 * </code>
 *
 * @package Phalcon\Db\Profiler
 */
class QueryLogger
{
    /**
     * @var \Phalcon\Logger
     */
    protected $logger;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var \Phalcon\Db\Profiler
     */
    protected $profiler;

    /**
     * QueryLogger constructor.
     *
     * @param \Phalcon\Logger      $logger   optional, \Phalcon\Logger instance, defaults to Firephp adapter.
     * @param \Phalcon\Db\Profiler $profiler optional, \Phalcon\Db\Profiler instance
     * @param int                  $priority optional, defaults to \Phalcon\Logger::DEBUG
     */
    public function __construct(
        \Phalcon\Logger $logger = null,
        \Phalcon\Db\Profiler $profiler = null,
        $priority = \Phalcon\Logger::DEBUG
    ) {
        $this->logger = $logger;
        if (!$this->logger) {
            $this->logger = new \Phalcon\Logger\Adapter\Firephp();
        }

        $this->profiler = $profiler;
        if (!$this->profiler) {
            $this->profiler = new \Phalcon\Db\Profiler();
        }

        $this->priority = $priority;
    }

    /**
     * Logger setter method.
     *
     * @param \Phalcon\Logger $logger logger instance
     *
     * @return $this
     */
    public function setLogger(\Phalcon\Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Logger getter method.
     *
     * @return \Phalcon\Logger|\Phalcon\Logger\Adapter\Firephp
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Profiler setter method.
     *
     * @param \Phalcon\Db\Profiler $profiler profiler instance
     *
     * @return $this
     */
    public function setProfiler(\Phalcon\Db\Profiler $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }

    /**
     * Profiler getter method.
     *
     * @return \Phalcon\Db\Profiler
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * Priority setter method.
     *
     * @param int $priority priority level, one of \Phalcon\Logger constants
     *
     * @return $this
     */
    public function setPriority($priority = \Phalcon\Logger::DEBUG)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Priority getter method.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Executed before query is executed.
     *
     * @param \Phalcon\Events\Event $event      event
     * @param \Phalcon\Db\Adapter   $connection connection
     *
     * @return void
     */
    public function beforeQuery(\Phalcon\Events\Event $event, \Phalcon\Db\Adapter $connection)
    {
        if ($event->getType() !== 'beforeQuery') {
            throw new \LogicException('Method is expected to be called only on "beforeQuery" event.');
        }

        $this->profiler->startProfile(
            $connection->getSQLStatement(),
            $connection->getSQLVariables(),
            $connection->getSQLBindTypes()
        );
    }

    /**
     * Executed after query is executed.
     * Logs compiled message to logger instance.
     *
     * @param \Phalcon\Events\Event $event      event
     * @param \Phalcon\Db\Adapter   $connection connection
     *
     * @return void
     */
    public function afterQuery(\Phalcon\Events\Event $event, \Phalcon\Db\Adapter $connection)
    {
        if ($event->getType() !== 'afterQuery') {
            throw new \LogicException('Method is expected to be called only on "afterQuery" event.');
        }

        $this->profiler->stopProfile();
        $descriptor = $connection->getDescriptor();
        $host = $descriptor['host'];
        $this->logger->log(
            $this->priority,
            json_encode(
                array(
                    'sql' => $this->profiler->getLastProfile()->getSQLStatement(),
                    'vars' => $this->profiler->getLastProfile()->getSQLVariables(),
                    'execution_time' => $this->profiler->getLastProfile()->getTotalElapsedSeconds(),
                    'bind_types' => $this->profiler->getLastProfile()->getSQLBindTypes(),
                    'host' => $host,
                ),
                JSON_FORCE_OBJECT
            )
        );
    }
}
