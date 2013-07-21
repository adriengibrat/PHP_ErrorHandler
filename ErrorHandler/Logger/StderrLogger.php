<?php

namespace ErrorHandler\Logger;

use \Psr\Log\AbstractLogger;
use \RuntimeException;

class StderrLogger extends AbstractLogger
{

    private $file;

    use LoggerHelperTrait;

    public function __construct($label = null)
    {
        $this->label = $label;
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkSeverity($level);

        file_put_contents('php://stderr', $this->interpolate($message, $context));
    }

}
