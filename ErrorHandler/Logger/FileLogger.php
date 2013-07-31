<?php

namespace ErrorHandler\Logger;

use \Psr\Log\LogLevel;

class FileLogger extends StreamLogger
{

    public function __construct($file, $label = null)
    {
        $path = dirname($file);
        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            throw new RuntimeException('Unable to create path');
        }

/*
        $handle = fopen($file, 'a+');
        if (!$handle) {
            throw new RuntimeException('Unable to create file');
        } else {
            fclose($handle);
        }
*/

        parent::__construct(realpath($file), $label);
    }
/*
    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        $handle = fopen($this->stream, 'a+');

        if (!$handle) {
            throw new RuntimeException('Unable to open file for writing');
        }

        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException ('Unable to lock file');
        }

        fwrite($handle, $this->interpolate($message, $context, $level));
        flock($handle, LOCK_UN);
        fclose($handle);
    }
*/
}
