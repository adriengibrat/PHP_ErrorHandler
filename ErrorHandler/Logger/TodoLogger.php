<?php

namespace ErrorHandler\Logger;

use \Psr\Log\AbstractLogger;
use \RuntimeException;

class XXXLogger extends AbstractLogger
{

    use LoggerHelperTrait;

    public function __construct($label = null)
    {
        $this->label = $label;
    }

    public function log($level, $message, array $context = array())
    {
        $this->checkSeverity($level);

    }

}

class Mongo {
    public static function init ($server, $database, $collection) {
        if ($server instanceof \Mongo) {
            $db = $server->{$database};
        } else {
            $conn = new \Mongo ($server);
            $db = $conn->{$database};
        }
        return function ($info) use ($db, $collection) {
            $db->{$collection}->insert ($info);
        };
    }
}
class Mail {
    public static function init ($to, $subject, $from) {
        return function ($info, $buffered = false) use ($to, $subject, $from) {
            $headers = sprintf ("From: %s\r\nContent-type: text/plain; charset=utf-8\r\n", $from);
            $body = ($buffered)
                ? "Logged:\n" . $info
                : vsprintf ("Machine: %s\nDate: %s\nLevel: %d\nMessage: %s", $info);

            mail ($to, $subject, wordwrap ($body, 70), $headers);
        };
    }
}
class REST {

}
class FTP {

}
class UDP {

}
class TCP {

}