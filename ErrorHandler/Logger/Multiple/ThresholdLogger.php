<?php

namespace ErrorHandler\Logger\Multiple;

use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;
use \ErrorHandler\Logger\Helper\LoggerTrait;
use \OutOfRangeException;

class ThresholdLogger extends MultipleLogger
{

    const EMERGENCY = 7;
    const ALERT     = 6;
    const CRITICAL  = 5;
    const ERROR     = 4;
    const WARNING   = 3;
    const NOTICE    = 2;
    const INFO      = 1;
    const DEBUG     = 0;

    protected $map = array(
        LogLevel::EMERGENCY => ThresholdLogger::EMERGENCY,
        LogLevel::ALERT     => ThresholdLogger::ALERT,
        LogLevel::CRITICAL  => ThresholdLogger::CRITICAL,
        LogLevel::ERROR     => ThresholdLogger::ERROR,
        LogLevel::WARNING   => ThresholdLogger::WARNING,
        LogLevel::NOTICE    => ThresholdLogger::NOTICE,
        LogLevel::INFO      => ThresholdLogger::INFO,
        LogLevel::DEBUG     => ThresholdLogger::DEBUG
    );

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        foreach($this->loggers as $limit => $logger)
        {
            if ($limit >= $this->map[$level]) {
                $logger->log($level, $message, $context);
            }
        }
    }

    public function addLogger(LoggerInterface $logger, $limit)
    {
        if (is_string($limit) && array_key_exists($limit, $this->map)) {
            $limit = $this->map[$limit];
        } else if (!is_int($limit) || 0 > $limit || $limit > 7) {
            throw new OutOfRangeException(
                'Invalid logging threshold limit'
            );
        }

        $this->loggers[$limit] = $logger;

        return $this;
    }

}
