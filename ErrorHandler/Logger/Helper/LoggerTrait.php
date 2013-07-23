<?php

namespace ErrorHandler\Logger\Helper;

use \Psr\Log\LogLevel;
use \Psr\Log\InvalidArgumentException;

trait LoggerTrait
{

    private $label;

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

    protected function interpolate($message, array $context = array())
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
