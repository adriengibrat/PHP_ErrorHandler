<?php

namespace ErrorHandler\Logger;

class ArrayLogger extends AbstractLogger
{

    private $array;

    public function __construct(array &$array, $label = null)
    {
        $this->array &= $array;

        parent::__construct($label);
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        $this->array[] = $this->interpolate($message, $context, $level);
    }

}
