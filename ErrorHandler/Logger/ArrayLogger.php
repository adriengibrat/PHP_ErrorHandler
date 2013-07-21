<?php

namespace ErrorHandler\Logger;

use \Psr\Log\AbstractLogger;
use \RuntimeException;

class ArrayLogger extends AbstractLogger
{
    private $array;

    use LoggerHelperTrait;

    public function __construct(array &$array, $label = null)
    {
        $this->array &= $array;
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkSeverity($level);

        $this->array[] = $this->interpolate($message, $context);
    }

}
