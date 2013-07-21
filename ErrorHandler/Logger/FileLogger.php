<?php

namespace ErrorHandler\Logger;

use \Psr\Log\AbstractLogger;
use \RuntimeException;

class FileLogger extends AbstractLogger
{

    private $label;

    private $file;

    use LoggerHelperTrait;

    public function __construct($file, $label = null)
    {
        $this->file  = realpath($file);
        if (!file_exists($this->file)) {
            throw new RuntimeException('Unable to find file');
        }

        $this->label = $label;
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkSeverity($level);

        $handle = fopen($this->file, 'a+');

        if (!$handle) {
            throw new RuntimeException('Unable to open file for writing');
        }

        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException ('Unable to lock file');
        }

        fwrite($handle, $this->interpolate($message, $context));
        flock($handle, LOCK_UN);
        fclose($handle);
    }

}
