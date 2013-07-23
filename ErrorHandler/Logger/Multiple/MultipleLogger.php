<?php

namespace ErrorHandler\Logger\Multiple;

use \ArrayAccess;
use \Countable;
use \ErrorHandler\Logger\AbstractLogger;
use \Psr\Log\LoggerInterface;

class MultipleLogger extends AbstractLogger implements ArrayAccess, Countable
{

    protected $loggers;

    public function __construct(array $loggers = array())
    {
        foreach ($loggers as $index => $logger) {
            $this->offsetSet($index, $logger);
        }
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        foreach($this->loggers as $logger)
        {
            $logger->log($level, $message, $context);
        }
    }

    public function addLogger(LoggerInterface $logger, $index)
    {
        if (is_null($index)) {
            $this->loggers[] = $logger;
        } else {
            $this->loggers[$index] = $logger;
        }
        return $this;
    }

    public function offsetExists($index)
    {
        return array_key_exists($index, $this->loggers);
    }

    public function offsetGet($index)
    {
        return $this->offsetExists($index) ?
            $this->loggers[$index] :
            null;
    }

    public function offsetSet($index, $logger)
    {
        return $this->addLogger($logger, $index);
    }

    public function offsetUnset($index)
    {
        unset($this->loggers[$index]);
        return $this;
    }

    public function count()
    {
        return count($this->loggers);
    }

}