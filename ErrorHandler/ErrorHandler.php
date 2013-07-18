<?php
/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2013 Steven King <info@k1ngdom.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category   Debugger
 * @package    PHP ErrorHandler
 * @author     Steven King <info@k1ngdom.net>
 * @copyright  2012 Steven King.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    0.1
 */

if (class_exists('FirePHP') === false) {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Plugins' . DIRECTORY_SEPARATOR . 'FirePHP.class.php'
}

/**
 * Server Side Error Handler class.
 */
class ErrorHandler {
    /**
     * @var array
     */
    protected $setting = array(
        'errorLevel'   => E_ALL,
        'displayError' => false,
        'logToConsole' => false,
        'logFile'      => false,
        'dateFormat'   => 'd-m-Y H:i:s',
        'sysLogIdent'  => false,
        'catchFatal'   => true,
        'documentRoot' => null
    );

    protected $ini = array(
        'error_reporting'        => -1,
        'report_memleaks'        => 1,
        'ignore_repeated_errors' => 1,
        'ignore_repeated_source' => 1,
        'track_errors'           => 1,
        'html_errors'            => 0,
        'docref_root'            => 'http://php.net/manual/en/',
        'docref_ext'             => '.php',
        'log_errors_max_len'     => 0,
        'display_errors'         => 0
    );

    protected $template = array(
        'displayError'     => "<pre><font color='#cc0000'><strong>%error%</strong>: %message% in <strong>%file%</strong> on line <em>%line%</em></font></pre>\r\n",
        'displayException' => "<pre><font color='#cc0000'>Uncaught <strong>%class%</strong>: %message% on <strong>%file%</strong> in line <em>%line%</em></font></pre>\r\n",
        'cliError'         => "%error%: %message% in %file% on line %line%\r\n",
        'cliException'     => "Uncaught %class%: %message% on %file% in line %line%\r\n",
        'writeError'       => "[%datetime%] %error%: %message% in %file% on line %line%\r\n",
        'writeException'   => "[%datetime%] Uncaught %class%: %message% on %file% in line %line%\r\n",
        'firePHPError'     => '%error%: %message% in %file% on line %line%',
        'firePHPException' => 'Uncaught %class%: %message% on %file% in line %line%',
        'syslogError'      => '%error%: %message% in %file% on line %line%',
        'syslogException'  => 'Uncaught %class%: %message% on %file% in line %line%'
    );

    private $error = array(
        0                     => 'Unknown'
        , E_ERROR             => 'Fatal'
        , E_RECOVERABLE_ERROR => 'Recoverable'
        , E_WARNING           => 'Warning'
        , E_PARSE             => 'Parse'
        , E_NOTICE            => 'Notice'
        , E_STRICT            => 'Strict'
        , E_DEPRECATED        => 'Deprecated'
        , E_CORE_ERROR        => 'Fatal'
        , E_CORE_WARNING      => 'Warning'
        , E_COMPILE_ERROR     => 'Compile Fatal'
        , E_COMPILE_WARNING   => 'Compile Warning'
        , E_USER_ERROR        => 'Fatal'
        , E_USER_WARNING      => 'Warning'
        , E_USER_NOTICE       => 'Notice'
        , E_USER_DEPRECATED   => 'Deprecated'
    );

    private $original = array();

    /**
     * @var string
     */
    const EXCEPTION = 'exception';

    /**
     * @var string
     */
    const ERROR = 'error';

    /**
     * The Constructor.
     * @param int     $errorLevel   error_reporting level. (See http://php.net/manual/en/function.error-reporting.php).
     */
    public function __construct($errorLevel = null) {
        /* Default settings. */
        $this->setting['errorLevel']   = is_null($errorLevel) ? ini_get('error_reporting') : (int) $errorLevel;
        $this->setting['documentRoot'] = str_replace(array('/','\\'), DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/');

        foreach ($this->ini as $setting => $value) {
            $this->original[$setting] = ini_get($setting);
            ini_set($setting, $value);
        }

        self::_disableXdebug();
        set_error_handler(array($this, 'handleErrors'), $this->setting['errorLevel']);
        set_exception_handler(array($this, 'handleExceptions'));
        register_shutdown_function(array($this, 'handleFatal'));
    }

    /**
     * Magic method for Settings
     *
     * @param  string $setting   Name of the setting.
     * @param  mixed  $arguments Value of setting.
     * @return ErrorHandler      Fluent interface.
     */
    public function __call ($setting, $arguments) {
        return $this->set($setting, $arguments[0]);
    }

    /**
     * Fatal Error Handler.
     */
    public function handleFatal() {
        if ($this->setting['catchFatal'] === true) {
            return;
        }
        $error = error_get_last();
        if (!empty($error)) {
            /** Get an array of arguments of error_get_last() **/
            if(!isset($error['errcontext']))
                $error['errcontext'] = '';
            /* Send Errors to firePHP and chromePhp */
            if ($this->setting['logToConsole']) {
                $this->_logToFirePHP(self::ERROR, $error);
            }
            /* Display Errors */
            if ($this->setting['displayError']) {
                $this->_displayError(self::ERROR, $error);
            }
            /* Write Errors to file. */
            if ($this->setting['logFile']) {
                $this->_logToFile(self::ERROR, $error);
            }
            /* Send Errors to Syslog */
            if ($this->setting['sysLogIdent']) {
                $this->_logToSyslog(self::ERROR, $error);
            }
        }
    }

    /**
     * This function can be used for defining your own way of handling errors during runtime.
     *
     * @param string $errno      The level of the error raised.
     * @param string $errstr     The error message.
     * @param string $errfile    The filename that the error was raised in.
     * @param int    $errline    The line number the error was raised at.
     * @param string $errcontext Optional. An array that points to the active symbol table at the point the error occurred.
     */
    public function handleErrors($errno, $errstr, $errfile, $errline, $errcontext) {
        /* if error has been supressed with an @ */
        if (error_reporting() === 0) {
            return true;
        }
        /** Get an array of arguments of this function **/
        $error = array(
            'type'    => $errno,
            'message' => $errstr,
            'file'    => $errfile,
            'line'    => $errline,
            'context' => $errcontext
        );
        try {
            /* Send Errors to firePHP and chromePhp */
            if ($this->setting['logToConsole']) {
                $this->_logToFirePHP(self::ERROR, $error);
            }
            /* Display Errors */
            if ($this->setting['displayError']) {
                $this->_displayError(self::ERROR, $error);
            }
            /* Write Errors to file. */
            if ($this->setting['logFile']) {
                $this->_logToFile(self::ERROR, $error);
            }
            /* Send Errors to Syslog */
            if ($this->setting['sysLogIdent']) {
                $this->_logToSyslog(self::ERROR, $error);
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
        return true;
    }

    /**
     * Exception Handler function.
     *
     * @param object $exception Arguments passed by set_exception_handler() function
     */
    public function handleExceptions ($exception) {
        try {
            /* Send Errors to firePHP and chromePhp */
            if ($this->setting['logToConsole']) {
                $this->_logToFirePHP(self::EXCEPTION, $exception);
            }
            /* Display Errors */
            if ($this->setting['displayError']) {
                $this->_displayError(self::EXCEPTION, $exception);
            }
            /* Write Errors to file. */
            if ($this->setting['logFile']) {
                $this->_logToFile(self::EXCEPTION, $exception);
            }
            /* Send Errors to Syslog */
            if ($this->setting['sysLogIdent']) {
                $this->_logToSyslog(self::EXCEPTION, $exception);
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Revert to the previous error handler (which could be the built-in or a user defined function).
     */
    public function destroy() {
        restore_error_handler();
        restore_exception_handler();
        foreach ($this->original as $setting => $value) {
            ini_set($setting, $value);
        }
        $this->setting['catchFatal'] = false;
    }

    /**
     * Restore ErrorHandler after calling the destroy().
     */
    public function restore() {
        set_error_handler(array($this, 'handleErrors'), $this->setting['errorLevel']);
        set_exception_handler(array($this, 'handleExceptions'));
        foreach ($this->ini as $setting => $value) {
            ini_set($setting, $value);
        }
        $this->setting['catchFatal'] = true;
    }

    /**
     * Writes errors to an error log file.
     *
     * @param string $type This can be either self::ERROR or self::EXCEPTION
     * @param mixed  $error Arguments of HandleException or HandleError
     */
    private function _logToFile($type, $error) {
        switch ($type) {
            case self::ERROR:
                $ErrorLog = $this->_lineFormat('writeError', $error);
                break;
            case self::EXCEPTION:
                $ErrorLog = $this->_lineFormat('writeException', $error, true);
                break;
        }

        if (@file_put_contents($this->setting['logFile'], $ErrorLog, FILE_APPEND|LOCK_EX) === false) {
            throw new Exception('Unable to write to log file. Please check file permission.');
        }
    }

    /**
     * Display errors on web page. I do not recommend this to be turned on a production site.
     *
     * @param string $type This can be either self::ERROR or self::EXCEPTION
     * @param mixed  $error Arguments of HandleException or HandleError
     */
    private function _displayError($type, $error) {
        $isCLI = php_sapi_name() === 'cli';
        switch ($type) {
            case self::ERROR:
                echo $this->_lineFormat($isCLI ? 'cliError' : 'displayError', $error);
                break;
            case self::EXCEPTION:
                echo $this->_lineFormat($isCLI ? 'cliException' : 'displayException', $error, true);
                break;
        }
    }

    /**
     * Display errors on Browser's Console. (Plugin for browsers are required).
     *
     * @param string $type This can be either self::ERROR or self::EXCEPTION
     * @param mixed  $error Arguments of HandleException or HandleError
     */
    private function _logToFirePHP($type, $error) {
        /** Check if Header is already sent. **/
        if (headers_sent() === true) {
            return false;
        }

        switch ($type) {
            case self::ERROR:
                switch($error['errno']) {
                    case E_ERROR:
                    case E_CORE_ERROR:
                        $logType =  'error';
                        break;
                    case E_WARNING:
                    case E_USER_ERROR:
                        $logType = 'warn';
                        break;
                    case E_USER_NOTICE:
                    case E_NOTICE:
                    case E_DEPRECATED:
                    default:
                        $logType = 'info';
                }
                $errorLog = $this->_lineFormat('firePHPError', $error);
                $meta = array(
                    'File' => $error['file'],
                    'Line' => $error['line']
                );
                break;
            case self::EXCEPTION:
                $logType =  'error';
                $errorLog = $this->_lineFormat('firePHPException', $error, true);
                $meta = array(
                    'File' => $error->getFile(),
                    'Line' => $error->getLine()
                );
                break;
        }

        if (class_exists('FirePHP')) {
            call_user_func(array(FirePHP::getInstance(true), $logType), $errorLog, null, $meta);
        } else  {
            throw new Exception('Unable to log with FirePHP. Please check FirePHP class is included.');
        }

        return true;
    }

    /**
     * Log to Syslog.
     *
     * @param string $type This can be either self::ERROR or self::EXCEPTION
     * @param mixed  $error Arguments of HandleException or HandleError
     */
    private function _logToSyslog($type, $error) {

        if (openlog($this->setting['sysLogIdent'], LOG_PERROR | LOG_ODELAY | LOG_PID, LOG_USER) === false) {
            throw new Exception('Unable to open syslog');
        }

        switch ($type) {
            case self::ERROR:
                switch($error['errno']) {
                    case E_ERROR:
                    case E_CORE_ERROR:
                        $LogType =  LOG_ERR;
                        break;
                    case E_WARNING:
                    case E_USER_ERROR:
                        $LogType = LOG_WARNING;
                        break;
                    case E_USER_NOTICE:
                    case E_NOTICE:
                    case E_DEPRECATED:
                    case E_STRICT:
                        $LogType = LOG_NOTICE;
                        break;
                    default:
                        $LogType = LOG_NOTICE;
                }
                $ErrorLog = $this->_lineFormat('syslogError', $error);
                break;
            case self::EXCEPTION:
                $LogType =  LOG_ERR;
                $ErrorLog = $this->_lineFormat('syslogException', $error, true);
                break;
        }

        syslog($LogType, $ErrorLog);
        closelog();
    }

    /**
     * Set custom settings
     *
     * @param string $setting Name of the setting.
     * @param mixed  $value   Value of the setting.
     * @return ErrorHandler   Fluent interface.
     */
    public function set($setting, $value) {
        if (array_key_exists($setting, $this->setting)) {
            $this->setting[$setting] = $value;
        }
        return $this;
    }

    /**
     * Format the templates.
     * @param string  $template Template name.
     * @param mixed   $error    Exception or array passed by error handler.
     */
    private function _lineFormat($format, $error) {
        if ($error instanceof Exception) {
            $template = array(
                '%datetime%' => date($this->setting['dateFormat']),
                '%class%'    => get_class($error),
                '%message%'  => str_replace($this->setting['documentRoot'], '', $error->getMessage()),
                '%fullpath%' => $error->getFile(),
                '%file%'     => str_replace($this->setting['documentRoot'], '', $error->getFile()),
                '%line%'     => $error->getLine(),
                '%code%'     => $error->getCode()
            );
        } else {
            $template = array(
                '%datetime%' => date($this->setting['dateFormat']),
                '%error%'    => array_key_exists($error['type'], $this->error) ?
                    $this->error[$error['type']]:
                    $this->error[0] . ' ' . $error['type'],
                '%message%'  => str_replace($this->setting['documentRoot'], '', $error['message']),
                '%fullpath%' => $error['file'],
                '%file%'     => str_replace($this->setting['documentRoot'], '', $error['file']),
                '%line%'     => $error['line'],
                '%context%'  => print_r($error['context'], true)
            );
        }

        $format = $this->template[$format];
        foreach ($template as $placeholder => $value) {
            $format = str_replace($placeholder, $value, $format);
        }

        return $format;
    }

    /**
     * Disable the xDebug if its enabled.
     */
    private static function _disableXdebug() {
        if (function_exists('xdebug_is_enabled') === true && xdebug_is_enabled() === true) {
            xdebug_disable();
        }
    }

}
