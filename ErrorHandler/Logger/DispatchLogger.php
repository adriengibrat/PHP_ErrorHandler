<?php

namespace ErrorHandler\Logger;

use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;
use \OutOfRangeException;

class DispatchLogger extends MultiLogger
{
    const EMERGENCY = 1;
    const ALERT     = 2;
    const CRITICAL  = 4;
    const ERROR     = 8;
    const WARNING   = 16;
    const NOTICE    = 32;
    const INFO      = 64;
    const DEBUG     = 128;

    private $levels = array(
        LogLevel::EMERGENCY => DispatchLogger::EMERGENCY,
        LogLevel::ALERT     => DispatchLogger::ALERT,
        LogLevel::CRITICAL  => DispatchLogger::CRITICAL,
        LogLevel::ERROR     => DispatchLogger::ERROR,
        LogLevel::WARNING   => DispatchLogger::WARNING,
        LogLevel::NOTICE    => DispatchLogger::NOTICE,
        LogLevel::INFO      => DispatchLogger::INFO,
        LogLevel::DEBUG     => DispatchLogger::DEBUG
    );

    use LoggerHelperTrait;

    public function log($level, $message, array $context = array())
    {
        $this->checkSeverity($level);

        foreach($this->loggers as $flag => $logger)
        {
            if ($flag & $this->levels[$level]) {
                $logger->log($level, $message, $context);
            }
        }
    }

    public function offsetSet($flag, LoggerInterface $logger)
    {
        if (!is_int($flag) && (1 > $flag || $flag > 255)) {
            throw new OutOfRangeException(
                'Invalid logging trigger flag'
            );
        }
        $this->loggers[$flag] = $value;
        return $this;
    }

}
