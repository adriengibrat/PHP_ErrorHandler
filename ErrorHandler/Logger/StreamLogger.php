<?php

namespace ErrorHandler\Logger;

use \Psr\Log\LogLevel;
use \RuntimeException;

class StreamLogger extends AbstractLogger
{

    protected $stream;
    
    private  $meta;

    static public $format = array(
		LogLevel::EMERGENCY => "%s %s\t%s\n",
        LogLevel::ALERT     => "%s %s\t%s\n",
        LogLevel::CRITICAL  => "%s %s\t%s\n",
        LogLevel::ERROR     => "%s %s\t%s\n",
        LogLevel::WARNING   => "%s %s\t%s\n",
        LogLevel::NOTICE    => "%s %s\t%s\n",
        LogLevel::INFO      => "%s %s\t%s\n",
        LogLevel::DEBUG     => "%s %s\t%s\n"
    );

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

    protected function interpolate($message, array $context, $level)
    {
        return sprintf(static::$format[$level],
			date('Y-m-d H:i:s'),
			$level,
			parent::interpolate($message, $context, $level)
		);
    }

}
