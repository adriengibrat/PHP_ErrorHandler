<?php

namespace ErrorHandler\Logger;

use \RuntimeException;

class StreamLogger extends AbstractLogger
{

    protected $stream;

    public function __construct($stream, $label = null)
    {
        $this->stream = $stream;

        parent::__construct($label);
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        file_put_contents($this->stream, $this->interpolate($message, $context));
    }

}
