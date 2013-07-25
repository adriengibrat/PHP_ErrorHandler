<?php

namespace ErrorHandler\Logger;

use \Psr\Log\LogLevel;
use \Psr\Log\AbstractLogger as PsrAbstractLogger;
use \Psr\Log\InvalidArgumentException;

abstract class AbstractLogger extends PsrAbstractLogger
{

    protected $label;

    protected $levels = array(
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG
    );

    public function __construct($label = null)
    {
        $this->label = $label;
    }

    protected function interpolate($message, array $context, $level)
    {
        $message = (string) $message;
        if (!empty($this->label)) {
        	$message = sprintf('[%s] %s', $this->label, $message);
        }
        array_walk($context, function($value, $key) {
            $message = str_replace('{' . $key . '}', $value, $message);
        });
        return $message;
    }

    protected function checkLevel($level)
    {
        if (!in_array($level, $this->levels)) {
            throw new InvalidArgumentException(
                'Unknown severity level'
            );
        }
    }

}
