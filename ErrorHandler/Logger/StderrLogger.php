<?php

namespace ErrorHandler\Logger;

use \Psr\Log\LogLevel;

class StderrLogger extends StreamLogger
{

    protected $stream = 'php://stderr';
    
    static public $format = array(
		LogLevel::EMERGENCY => "\033[37m%s \033[33;1;41m%s\033[0m\t%s\n",
        LogLevel::ALERT     => "\033[37m%s \033[37;1;41m%s\033[0m\t%s\n",
        LogLevel::CRITICAL  => "\033[37m%s \033[30;41m%s\033[0m\t%s\n",
        LogLevel::ERROR     => "\033[37m%s \033[31;1m%s\033[0m\t%s\n",
        LogLevel::WARNING   => "\033[37m%s \033[31m%s\033[0m\t%s\n",
        LogLevel::NOTICE    => "\033[37m%s \033[31;2m%s\033[0m\t%s\n",
        LogLevel::INFO      => "\033[37m%s \033[37;1m%s\033[0m\t%s\n",
        LogLevel::DEBUG     => "\033[37m%s %s\033[0m\t%s\n"
    );

    public function __construct($label = null)
    {
        $this->label = $label;
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        file_put_contents($this->stream, $this->interpolate($message, $context, $level));
    }

}
