<?php

namespace ErrorHandler\Logger;

use \RuntimeException;

class StderrLogger extends StreamLogger
{

    protected $stream = 'php://stderr';

    public function __construct($label = null)
    {
        $this->label = $label;
    }

}
