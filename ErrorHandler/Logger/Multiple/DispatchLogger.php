<?php

namespace ErrorHandler\Logger\Multiple;

use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;
use \ErrorHandler\Logger\Helper\LoggerTrait;
use \OutOfRangeException;

class DispatchLogger extends MultipleLogger
{

    const EMERGENCY = 1;
    const ALERT     = 2;
    const CRITICAL  = 4;
    const ERROR     = 8;
    const WARNING   = 16;
    const NOTICE    = 32;
    const INFO      = 64;
    const DEBUG     = 128;

    protected $map = array(
        LogLevel::EMERGENCY => DispatchLogger::EMERGENCY,
        LogLevel::ALERT     => DispatchLogger::ALERT,
        LogLevel::CRITICAL  => DispatchLogger::CRITICAL,
        LogLevel::ERROR     => DispatchLogger::ERROR,
        LogLevel::WARNING   => DispatchLogger::WARNING,
        LogLevel::NOTICE    => DispatchLogger::NOTICE,
        LogLevel::INFO      => DispatchLogger::INFO,
        LogLevel::DEBUG     => DispatchLogger::DEBUG
    );

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        foreach($this->loggers as $flag => $logger)
        {
            if ($flag & $this->map[$level]) {
                $logger->log($level, $message, $context);
            }
        }
    }

    public function addLogger(LoggerInterface $logger, $flag)
    {
        if (is_string($flag)) {
            $flag = array_reduce(explode('|', $flag), function ($flag, $level) {
                return array_key_exists($level, $this->map) ? $flag | $this->map[$level] : -1;
            }, 0);
        }

        if (!is_int($flag) || 1 > $flag || $flag > 255) {
            throw new OutOfRangeException(
                'Invalid logging dispatch flag'
            );
        }

        $this->loggers[$flag] = $logger;

        return $this;
    }

}
