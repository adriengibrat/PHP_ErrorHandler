<?php

namespace ErrorHandler\Logger\Multiple;

use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;
use \OutOfRangeException;

class ThresholdLogger extends LevelLogger
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

        foreach($this->loggers as $threshold => $logger)
        {
            if ($threshold >= $this->map[$level]) {
                $logger->log($level, $message, $context);
            }
        }
    }

    public function addLogger(LoggerInterface $logger, $threshold)
    {
        if (is_string($threshold) && array_key_exists($threshold, $this->map)) {
            $threshold = $this->map[$threshold];
        } else if (!is_int($threshold) || 0 > $threshold || $threshold > 7) {
            throw new OutOfRangeException(
                'Invalid logging threshold limit'
            );
        }

        $this->loggers[$threshold] = $logger;

        return $this;
    }

}
