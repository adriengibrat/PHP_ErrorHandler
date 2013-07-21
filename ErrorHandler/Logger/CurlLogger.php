<?php

namespace ErrorHandler\Logger;

use \Psr\Log\AbstractLogger;
use \RuntimeException;

class CurlLogger extends AbstractLogger
{
    private $handle;

    use LoggerHelperTrait;

    public function __construct($handle, $label = null)
    {
        $this->setHandler($handle);
    }

    protected function setHandler($handle)
    {
        $this->handle = $handle;
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkSeverity($level);

        $this->
    }

}

            if (! extension_loaded ('curl')) {
                throw new \LogicException ('CURL extension not loaded.');
            }

            $ch = curl_init ();
            curl_setopt ($ch, CURLOPT_URL, $address);
            curl_setopt ($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_VERBOSE, 0);
            curl_setopt ($ch, CURLOPT_HEADER, 0);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $info);
            curl_exec ($ch);
            curl_close ($ch);