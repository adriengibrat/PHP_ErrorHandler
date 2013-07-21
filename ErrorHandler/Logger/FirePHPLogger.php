<?php

namespace ErrorHandler\Logger;

use \FirePHP;
use \Psr\Log\LogLevel;
use \Psr\Log\AbstractLogger;

class FirePHPLogger extends AbstractLogger
{

    private $label;

    private $levels = array (
        LogLevel::EMERGENCY => FirePHP::ERROR,
        LogLevel::ALERT     => FirePHP::ERROR,
        LogLevel::CRITICAL  => FirePHP::ERROR,
        LogLevel::ERROR     => FirePHP::ERROR,
        LogLevel::WARNING   => FirePHP::WARN,
        LogLevel::NOTICE    => FirePHP::INFO,
        LogLevel::INFO      => FirePHP::INFO,
        LogLevel::DEBUG     => FirePHP::LOG
    );

    use LoggerHelperTrait;

    public function __construct($label=null)
    {
        $this->label = $label;
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkSeverity($level);

        if (headers_sent() === true) {
            return;
        }

        $message = $this->interpolate($message, $context);

        $context = array_change_key_case($context);
        if (array_key_exists('file', $context) && array_key_exists('line', $context)) {
            $options = array(
                'File' => $context['file'],
                'Line' => $context['line']
            );
        } else if (array_key_exists('exception', $context) && $context['exception'] instanceof Exception ) {
            $options = array(
                'File' => $context['exception']->getFile(),
                'Line' => $context['exception']->getLine()
            );
        } else {
            $options = array();
        }
        
        FirePHP::getInstance(true)->fb($message, $this->label, $this->levels[$level], $options);
    }

}
