<?php

namespace ErrorHandler\Logger\Multiple;

use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;
use \ErrorHandler\Logger\Helper\LoggerTrait;
use \OutOfRangeException;

class LevelLogger extends MultipleLogger
{

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        if (array_key_exists($level, $this->loggers)) {
            $this->loggers[$level]->log($level, $message, $context);
        }
    }

    public function addLogger(LoggerInterface $logger, $level)
    {
        if (!in_array($level, $this->levels)) {
            throw new OutOfRangeException(
                'Invalid logging level'
            );
        }

        $this->loggers[$level] = $logger;

        return $this;
    }

}