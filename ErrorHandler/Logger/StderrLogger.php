<?php

namespace ErrorHandler\Logger;

use \Psr\Log\AbstractLogger;
use \ErrorHandler\Logger\Helper\LoggerTrait;
use \RuntimeException;

class StderrLogger extends AbstractLogger
{

    use LoggerTrait;

    private $file;

    public function __construct($label = null)
    {
        $this->label = $label;
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        file_put_contents('php://stderr', $this->interpolate($message, $context));
    }

}
