<?php
/**
 * User: tuttarealstep
 * Date: 26/04/17
 * Time: 20.23
 */

namespace Honey\Log;

use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    function __construct(MonologLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Monolog\Logger
     */
    public function getMonolog(): MonologLogger
    {
        return $this->logger;
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function emergency($message, array $context = array())
    {
        $this->sendLog(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function alert($message, array $context = array())
    {
        $this->sendLog(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function critical($message, array $context = array())
    {
        $this->sendLog(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = array())
    {
        $this->sendLog(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = array())
    {
        $this->sendLog(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function notice($message, array $context = array())
    {
        $this->sendLog(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = array())
    {
        $this->sendLog(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function debug($message, array $context = array())
    {
        $this->sendLog(__FUNCTION__, $message, $context);
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        $this->sendLog($level, $message, $context);
    }

    /**
     * @param $function
     * @param $message
     * @param array $context
     */
    protected function sendLog($function, $message, array $context = [])
    {
        $this->logger->{$function}($message, $context);
    }
}