<?php

namespace ErrorHandler\Logger;

use \Psr\Log\AbstractLogger;
use \ErrorHandler\Logger\Helper\LoggerTrait;
use \RuntimeException;

class ArrayLogger extends AbstractLogger
{

    use LoggerTrait;

    private $array;

    public function __construct(array &$array, $label = null)
    {
        $this->array &= $array;
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        $this->array[] = $this->interpolate($message, $context);
    }

}
