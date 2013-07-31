<?php

namespace ErrorHandler\Logger;

use \RuntimeException;

class StreamLogger extends AbstractLogger
{

    protected $stream;
    
    private  $meta;

    static public $format = "%s %s\t%s\n";

    public function __construct($stream = 'php://output', $label = null)
    {
        $this->stream = $stream;

        if (func_num_args()) {
            if (is_resource($this->stream)) {
                $this->meta = stream_get_meta_data($this->stream);
            } else {
                $handle = fopen($this->stream, 'r');
                $this->meta = stream_get_meta_data($handle);
                fclose($handle);
            }

            if (false === strstr($this->meta['mode'], 'w')) {
                throw new RuntimeException(
                    sprintf('Stream "%s" is not writable', $this->meta['uri'])
                );
            }
        }

        parent::__construct($label);
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        if (is_resource($this->stream)) {
            $wrote = fwrite($this->stream, $this->interpolate($message, $context, $level));
        } else {
            $wrote = file_put_contents($this->stream, $this->interpolate($message, $context, $level));
        }

        if (!$wrote) {
            throw new RuntimeException(
                sprintf('Unable to write to stream "%s"', $this->meta['uri'])
            );
        }
    }

    public function __destruct()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    } 

    protected function interpolate($message, array $context, $level)
    {
        if (is_string(static::$format)) {
            $format = static::$format;
        } else if (array_key_exists($level, static::$format)) {
            $format = static::$format[$level];
        } else {
            $format = "%s %s\t%s\n";
        }
        return sprintf($format,
            date('Y-m-d H:i:s'),
            $level,
            parent::interpolate($message, $context, $level)
        );
    }

}
