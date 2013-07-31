<?php

namespace ErrorHandler\Logger;

class OutputLogger extends StreamLogger
{
    protected $stream = 'php://output';

    static public $format = '<div class="log %2$s"><span class="date">%1$s</span> <em class="level">%2$-9s</em> <span class="message">%3$s</span></div>';

    public function __construct($label = null)
    {
        $this->label = $label;
        self::$format = trim(self::$format) . "\n";
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkLevel($level);

        file_put_contents($this->stream, $this->interpolate($message, $context, $level));
    }

    protected function interpolate($message, array $context, $level)
    {
		return nl2br(
			preg_replace_callback('!(?<uri>
					(?<protocol>(?:(?:http|ftp)s?|mailto|file)://)?
					(?<text>
						(?<url>
							[a-z][\w.-]*\.[a-z]{2,4}(?::\d+)?(?:/[\w/.?#-=&%]+)?
						)|(?<mail>
							[\w.-]+@[\w.-]+\.[a-z]{2,4}
						)|(?<path>
							(?<=\s|\/)\/\w[\w/.-]+
						)
					)
				)!x', function($matches) {
					if (empty($matches['protocol'])) {
						if (!empty($matches['url'])) {
							$matches['uri'] = 'http://' . $matches['url'];
						} else if (!empty($matches['mail'])) {
							$matches['uri'] = 'mailto://' . $matches['mail'];
						} else if (!empty($matches['path'])) {
							$matches['uri'] = 'file://' . $matches['path'];
						}
					}
					return sprintf('<a href="%s" target="_blank">%s</a>', $matches['uri'], $matches['text']);
				},
				parent::interpolate($message, $context, $level)
			)
		);
    }

}
