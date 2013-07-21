<?php

namespace ErrorHandler\Logger;

class ErrorHandlerLogger extends MultiLogger
{

    public function log($level, $message, array $context = array())
    {
        


        parent::log($level, $message, $context);
    }

}
