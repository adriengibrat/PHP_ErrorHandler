<?php

namespace ErrorHandler\Logger\Multiple;

class ErrorHandlerLogger extends MultipleLogger
{

    public function log($level, $message, array $context = array())
    {



        parent::log($level, $message, $context);
    }

}
