<?php

namespace ErrorHandler\Logger;

use \Psr\Log\AbstractLogger;
use \Psr\Log\LoggerInterface;

class MultiLogger extends AbstractLogger implement ArrayAccess, Countable
{
    protected $loggers;

    use LoggerHelperTrait;

    public function __construct(array $loggers = array())
    {
        foreach ($loggers as $index => $logger) {
            $this->offsetSet($index, $logger);
        }
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkSeverity($level);

        foreach($this->loggers as $logger)
        {
            $logger->log($level, $message, $context);
        }
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

    public function offsetSet($index, LoggerInterface $logger)
    {
        $this->loggers[$index] = $value;
        return $this;
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