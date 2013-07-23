<?php

namespace ErrorHandler\Logger;

use \RuntimeException;

class StderrLogger extends AbstractLogger
{

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
