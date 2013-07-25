<?php

namespace ErrorHandler\Logger;

use \Psr\Log\LogLevel;
use \InvalidArgumentException;
use \RuntimeException;

class SyslogLogger extends AbstractLogger
{

    private $facility;

    private $map = array (
        LogLevel::EMERGENCY => LOG_EMERG,
        LogLevel::ALERT     => LOG_ALERT,
        LogLevel::CRITICAL  => LOG_CRIT,
        LogLevel::ERROR     => LOG_ERR,
        LogLevel::WARNING   => LOG_WARNING,
        LogLevel::NOTICE    => LOG_NOTICE,
        LogLevel::INFO      => LOG_INFO,
        LogLevel::DEBUG     => LOG_DEBUG
    );

    public static $facilities = array (
        LOG_AUTH,
        LOG_AUTHPRIV,
        LOG_CRON,
        LOG_DAEMON,
        LOG_KERN,
        LOG_LPR,
        LOG_MAIL,
        LOG_NEWS,
        LOG_SYSLOG,
        LOG_USER,
        LOG_UUCP
    );

    public function __construct($facility = LOG_USER, $label = 'php')
    {
        if (!in_array($facility, static::$facilities, true)) {
            throw new InvalidArgumentException(
                    'Unknown facility value'
            );
        }

        $this->label    = $label;
        $this->facility = $facility;
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        if (!openlog($this->label, LOG_NDELAY | LOG_PID, $this->facility)) {
            throw new RuntimeException('Unable to open syslog');
        }

        syslog($this->map[$level], $this->interpolate($message, $context, $level));

        closelog();
    }

}

if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
    array_push(SyslogLogger::$facilities, LOG_LOCAL0, LOG_LOCAL1, LOG_LOCAL2, LOG_LOCAL3, LOG_LOCAL4, LOG_LOCAL5, LOG_LOCAL6, LOG_LOCAL7);
}
