<?php

namespace ErrorHandler\Logger\Multiple;

use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;
use \OutOfRangeException;

class DispatchLogger extends LevelLogger
{

    const EMERGENCY = 128;
    const ALERT     = 64;
    const CRITICAL  = 32;
    const ERROR     = 16;
    const WARNING   = 8;
    const NOTICE    = 4;
    const INFO      = 2;
    const DEBUG     = 1;

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
        if (func_num_args() > 2) {
            $flag = $this->computeFlag($this->flatten(array_slice(func_get_args(), 1)));
        } else if (is_int($flag)) {
            // shortcut
        } else if (is_array($flag)) {
            $flag = $this->computeFlag($this->flatten($flag));
        } else if (is_string($flag)) {
            $flag = $this->computeFlag(explode('|', $flag));
        }

        if (1 > $flag || $flag > 255) {
            throw new OutOfRangeException(
                'Invalid logging dispatch flag'
            );
        }

        $this->loggers[$flag] = $logger;

        return $this;
    }

    protected function flatten(Array $array)
    {
        return array_reduce($array, function ($flatten, $row) {
            if (is_array($row)) {
                return array_merge($flatten, $this->flatten($row));
            }
            array_push($flatten, $row);
            return $flatten;
        }, array());
    }

    protected function computeFlag(Array $flags)
    {
        return array_reduce($flags, function ($flag, $level) {
            if (!is_int($level)) {
                if (!array_key_exists($level, $this->map)) {
                    throw new OutOfRangeException(
                        'Invalid logging dispatch level'
                    );
                }
                $level = $this->map[$level];
            }
            return $flag | $level;
        }, 0);
    }

}
