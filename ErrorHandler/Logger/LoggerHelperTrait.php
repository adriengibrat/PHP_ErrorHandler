<?php

namespace ErrorHandler\Logger;

use \Psr\Log\LogLevel;
use \Psr\Log\InvalidArgumentException;

trait LoggerHelperTrait
{

    private $label;

    protected $levels = array(
        LogLevel::EMERGENCY => null,
        LogLevel::ALERT     => null,
        LogLevel::CRITICAL  => null,
        LogLevel::ERROR     => null,
        LogLevel::WARNING   => null,
        LogLevel::NOTICE    => null,
        LogLevel::INFO      => null,
        LogLevel::DEBUG     => null
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

    protected function checkSeverity($level)
    {
        if (!array_key_exists($level, $this->levels)) {
            throw new InvalidArgumentException(
                'Unknown severity level'
            );
        }
    }

}
