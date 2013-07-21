<?php

namespace ErrorHandler\Logger;

use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;
use \OutOfRangeException as InvalidLimit;

class ThresholdLogger extends MultiLogger
{

    protected $levels = array(
        LogLevel::EMERGENCY => 7,
        LogLevel::ALERT     => 6,
        LogLevel::CRITICAL  => 5,
        LogLevel::ERROR     => 4,
        LogLevel::WARNING   => 3,
        LogLevel::NOTICE    => 2,
        LogLevel::INFO      => 1,
        LogLevel::DEBUG     => 0
    );

    use LoggerHelperTrait;

    public function log($level, $message, array $context = array())
    {
        $this->checkSeverity($level);

        foreach($this->loggers as $limit => $logger)
        {
            if ($limit >= $this->levels[$level]) {
                $logger->log($level, $message, $context);
            }
        }
    }

    public function offsetSet($limit, LoggerInterface $logger)
    {
        if (!is_int($limit) && (0 > $limit || $limit > 7)) {
            throw new OutOfRangeException(
                'Invalid logging limit'
            );
        }
        $this->loggers[$flag] = $value;
        return $this;
    }

}
