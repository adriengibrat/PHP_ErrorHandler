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
 * @version    0.02
 * @link       http://k1ngdom.net
 */

if (class_exists('FB') === false) {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Plugins' . DIRECTORY_SEPARATOR . 'fb.php';
}

if (class_exists('ChromePhp')  === false) {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Plugins' . DIRECTORY_SEPARATOR . 'ChromePhp.php';
}


/**
 * Server Side Error Handler class.
 */
class ErrorHandler {
    /**
     * @var array
     */
    private $settings = array();

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
     * @param int     $ErrorLevel   error_reporting level. (See http://php.net/manual/en/function.error-reporting.php).
     */
    public function __construct($ErrorLevel = false) {
        /* Default settings. */
        $this->setting['ErrorLevel']   = is_bool($ErrorLevel) === false ? $ErrorLevel : ini_get('error_reporting');
        echo $this->setting['ErrorLevel'];
        $this->setting['DisplayError'] = false;
        $this->setting['LogToConsole'] = false;
        $this->setting['LogFile']      = '';
        $this->setting['DateFormat']   = 'd-m-Y H:i:s';
        $this->setting['SysLogIdent']  = false;
        $this->setting['DefElevel']    = ini_get('error_reporting');
        $this->setting['DefDisplay']    = ini_get('display_errors');
        $this->setting['CatchFatal']   = true;
        $this->setting['DocumentRoot'] = str_replace(array('/','\\'), DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/');

        // FIX.
        ini_set('display_errors', 0);
        error_reporting(-1);

        self::DisableXdebug();
        set_error_handler(array($this, 'HandleErrors'), $this->setting['ErrorLevel']);
        set_exception_handler(array($this, 'HandleExceptions'));
        register_shutdown_function(array($this, 'HandleFatal'));
    }


    /**
     * Magic method for Settings
     *
     * @param  string $name      name of the setting.
     * @param  mixed  $arguments value of setting.
     * @return boolean           returns true on success.
     */
    public function __call ($name, $arguments) {
        $AvailableSettings = array('DisplayError', 'LogToConsole', 'LogFile', 'DateFormat', 'SysLogIdent', 'ErrorLevel', 'CatchFatal');
        if (in_array($name, $AvailableSettings) === false) {
            return false;
        }

        if (count($arguments) <= 1) {
            $this->SetSetting($name, $arguments[0]);
        } else {
            $this->SetSetting($name, $arguments);
        }

        return true;
    }

    /**
     * Fatal Error Handler.
     */
    public function HandleFatal() {
        if ($this->setting['CatchFatal'] === true) {
            continue;
        }

        $error = error_get_last();

        if (is_null($error) === false) {
            /** Get an array of arguments of error_get_last() **/
            $args = array(
                'errno'      => $error["type"],
                'errstr'     => $error["message"],
                'errfile'    => $error["file"],
                'errline'    => $error["line"],
                'errcontext' => ''
                );


            /* Send Errors to firePHP and chromePhp */
            if ($this->setting['LogToConsole'] === true) {
                $this->LogToFirePHP(self::ERROR, $args);
            }

            /* Display Errors */
            if ($this->setting['DisplayError'] === true) {
                $this->DisplayError(self::ERROR, $args);
            }

            /* Write Errors to file. */
            if ($this->setting['LogFile'] !== false) {
                $this->LogToFile(self::ERROR, $args);
            }

            /* Send Errors to Syslog */
            if ($this->setting['SysLogIdent'] !== false) {
                $this->LogToSyslog(self::ERROR, $args);
            }
            //exit; // kill the script.
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
    public function HandleErrors($errno, $errstr, $errfile, $errline, $errcontext) {
        /* if error has been supressed with an @ */
        if (error_reporting() === 0) {
            return true;
        }

        /** Get an array of arguments of this function **/
        $args = array(
            'errno'      => $errno,
            'errstr'     => $errstr,
            'errfile'    => $errfile,
            'errline'    => $errline,
            'errcontext' => $errcontext
            );

        /* Send Errors to firePHP and chromePhp */
        if ($this->setting['LogToConsole'] === true) {
            $this->LogToFirePHP(self::ERROR, $args);
        }

        /* Display Errors */
        if ($this->setting['DisplayError'] === true) {
            $this->DisplayError(self::ERROR, $args);
        }

        /* Write Errors to file. */
        if ($this->setting['LogFile'] !== false) {
            $this->LogToFile(self::ERROR, $args);
        }

        /* Send Errors to Syslog */
        if ($this->setting['SysLogIdent'] !== false) {
            $this->LogToSyslog(self::ERROR, $args);
        }

        /* Don't execute PHP internal error handler */
        return true;
    }


    /**
     * Exception Handler function.
     *
     * @param object $exception Arguments passed by set_exception_handler() function
     */
    public function HandleExceptions ($exception) {

        /* Send Errors to firePHP and chromePhp */
        if ($this->setting['LogToConsole'] === true) {
            $this->LogToFirePHP(self::EXCEPTION, $exception);
        }

        /* Display Errors */
        if ($this->setting['DisplayError'] === true) {
            $this->DisplayError(self::EXCEPTION, $exception);
        }

        /* Write Errors to file. */
        if ($this->setting['LogFile'] !== false) {
            $this->LogToFile(self::EXCEPTION, $exception);
        }

        /* Send Errors to Syslog */
        if ($this->setting['SysLogIdent'] !== false) {
            $this->LogToSyslog(self::EXCEPTION, $exception);
        }
    }


    /**
     * Revert to the previous error handler (which could be the built-in or a user defined function).
     */
    public function Destroy() {
        restore_error_handler();
        restore_exception_handler();
        error_reporting($this->setting['DefElevel']);
        ini_set('display_errors', $this->setting['DefDisplay']);
        $this->setting['CatchFatal'] = false;
    }


    /**
     * Reinitiate ErrorHandler after calling the Destroy(). This maybe will be handy in future.
     */
    public function Reinitiate() {
        set_error_handler(array($this, 'HandleErrors'), $this->setting['ErrorLevel']);
        set_exception_handler(array($this, 'HandleExceptions'));
        error_reporting(-1);
        ini_set('display_errors', 0);
        $this->setting['CatchFatal'] = true;
    }


    /**
     * Writes errors to an error log file.
     *
     * @param string $type This can be either self:ERROR or self::EXCEPTION
     * @param mixed  $args Arguments of HandleException or HandleError
     */
    private function LogToFile($type, $args) {
        switch ($type) {
            case 'error':
                $ErrorLog = $this->LineFormat(self::GetTemplate('WriteError'), $args);
                break;
            case 'exception':
                $ErrorLog = $this->LineFormat(self::GetTemplate('WriteException'), $args, true);
                break;
        }

        if (@file_put_contents($this->setting['LogFile'], $ErrorLog, FILE_APPEND|LOCK_EX) === false) {
            throw new Exception("Unable to write to log file. Please check file permission.");
        }
    }


    /**
     * Display errors on web page. I do not recommend this to be turned on a production site.
     *
     * @param string $type This can be either self:ERROR or self::EXCEPTION
     * @param mixed  $args Arguments of HandleException or HandleError
     */
    private function DisplayError($type, $args) {
        switch ($type) {
            case 'error':
                echo $this->LineFormat(self::GetTemplate('DisplayError'), $args);
                break;
            case 'exception':
                echo $this->LineFormat(self::GetTemplate('DisplayException'), $args, true);
                break;
        }
    }


    /**
     * Display errors on Browser's Console. (Plugin for browsers are required).
     *
     * @param string $type This can be either self:ERROR or self::EXCEPTION
     * @param mixed  $args Arguments of HandleException or HandleError
     */
    private function LogToFirePHP($type, $args) {
        /** Check if Header is already sent. **/
        if (headers_sent() === true) {
            return false;
        }

        switch ($type) {
            case 'error':
                switch($args['errno']) {
                    case E_ERROR:
                    case E_CORE_ERROR:
                        $LogType =  'error';
                        break;
                    case E_WARNING:
                    case E_USER_ERROR:
                        $LogType = 'warn';
                        break;
                    case E_USER_NOTICE:
                    case E_NOTICE:
                    case E_DEPRECATED:
                        $LogType = 'info';
                        break;
                    default:
                        $LogType = 'info';
                }
                $ErrorLog = $this->LineFormat(self::GetTemplate('firePHPError'), $args);
                break;
            case 'exception':
                $LogType =  'error';
                $ErrorLog = $this->LineFormat(self::GetTemplate('firePHPException'), $args, true);
                break;
        }

        if (class_exists('FB') === true) {
            call_user_func(array('FB', $LogType), $ErrorLog);
        }

        if (class_exists('ChromePhp') === true) {
            call_user_func(array('ChromePhp', $LogType), $ErrorLog);
        }

        return true;
    }


    /**
     * Log to Syslog.
     *
     * @param string $type This can be either self:ERROR or self::EXCEPTION
     * @param mixed  $args Arguments of HandleException or HandleError
     */
    private function LogToSyslog($type, $args) {

        if (openlog($this->setting['SysLogIdent'], LOG_PERROR | LOG_ODELAY | LOG_PID, LOG_USER) === false) {
            throw new Exception("Can't open syslog");
        }

        switch ($type) {
            case 'error':
                switch($args['errno']) {
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
                $ErrorLog = $this->LineFormat(self::GetTemplate('SyslogError'), $args);
                break;
            case 'exception':
                $LogType =  LOG_ERR;
                $ErrorLog = $this->LineFormat(self::GetTemplate('SyslogException'), $args, true);
                break;
        }

        syslog($LogType, $ErrorLog);
        closelog();
    }


    /**
     * Set custom settings
     *
     * @param string $name  Name of the setting.
     * @param mixed  $value Value of the setting.
     */
    public function SetSetting($name, $value) {
        $this->setting[$name] = $value;
        return true;
    }


    /**
     * This method used for make the code nice and clean.
     * @param string $Name Name of the template to fetch.
     */
    private static function GetTemplate($Name) {
        $template = array(
            'DisplayError' => "<pre><font color='#cc0000'><strong>%error%</strong>: %message% in <strong>%file%</strong> on line <em>%line%</em></font></pre>\r\n",
            'DisplayException' => "<pre><font color='#cc0000'>Uncaught <strong>%class%</strong>: %message% on <strong>%file%</strong> in line <em>%line%</em></font></pre>\r\n",
            'CLIError' => "%error%: %message% in %file% on line %line%\r\n",
            'CLIException' => "Uncaught %class%: %message% on %file% in line %line%\r\n",
            'WriteError' => "[%datetime%] %error%: %message% in %file% on line %line%\r\n",
            'WriteException' => "[%datetime%] Uncaught %class%: %message% on %file% in line %line%\r\n",
            'firePHPError' => "%error%: %message% in %file% on line %line%\r\n",
            'firePHPException' => 'Uncaught %class%: %message% on %file% in line %line%',
            'SyslogError' => "%error%: %message% in %file% on line %line%",
            'SyslogException' => 'Uncaught %class%: %message% on %file% in line %line%'
            );

        if (array_key_exists($Name, $template) === false) {
            throw new Exception("Template $Name is not defined in GetTemplate.");
        }

        return $template[$Name];
    }


    /**
     * Format the templates.
     * @param string  $format    Templates from GetTemplate function.
     * @param mixed   $perms     Object or array passed by error handler.
     * @param boolean $exception set to true if its an exception template.
     */
    private function LineFormat($format, $perms, $exception = false) {
        /* Remove documment root from path. */

        if ($exception === false) {
            $template = array(
                '%datetime%' => date($this->setting['DateFormat']),
                '%error%'    => self::ErrorFriendlyName($perms['errno']),
                '%message%'  => str_replace($this->setting['DocumentRoot'], '', $perms['errstr']),
                '%fullpath%' => $perms['errfile'],
                '%file%'     => str_replace($this->setting['DocumentRoot'], '', $perms['errfile']),
                '%line%'     => $perms['errline'],
                '%context%'  => print_r($perms['errcontext'], true)
            );
        } else {
            $template = array(
                '%datetime%' => date($this->setting['DateFormat']),
                '%class%'    => get_class($perms),
                '%message%'  => str_replace($this->setting['DocumentRoot'], '', $perms->getMessage()),
                '%fullpath%' => $perms->getFile(),
                '%file%'     => str_replace($this->setting['DocumentRoot'], '', $perms->getFile()),
                '%line%'     => $perms->getLine(),
                '%code%'     => $perms->getCode()
                );
        }


        foreach ($template as $temp => $value) {
            $format = str_replace($temp, $value, $format);
        }

        return $format;
    }


    /**
     * Disable the xDebug if its enabled.
     */
    private static function DisableXdebug() {
        if (function_exists('xdebug_is_enabled') === true) {
            if (xdebug_is_enabled() === true) {
                xdebug_disable();
            }
        }
    }


    /**
     * Replace integer with human readable name.
     *
     * @param int $errno The level of the error raised.
     */
    private static function ErrorFriendlyName ($errno) {
        switch($errno) {
            case E_ERROR:
                return "Fatal Error";
                break;
            case E_WARNING:
                return "Warning";
                break;
            case E_PARSE:
                return "Parse Error";
                break;
            case E_NOTICE:
                return "Notice";
                break;
            case E_DEPRECATED:
                return "Deprecated";
                break;
            case E_CORE_ERROR:
                return "Core Error";
                break;
            case E_CORE_WARNING:
                return "Core Warning";
                break;
            case E_COMPILE_ERROR:
                return "Compile Error";
                break;
            case E_COMPILE_WARNING:
                return "Compile Warning";
                break;
            case E_USER_ERROR:
                return "User Error";
                break;
            case E_USER_WARNING:
                return "User Warning";
                break;
            case E_USER_NOTICE:
                return "User Notice";
                break;
            case E_STRICT:
                return "Strict Notice";
                break;
            case E_RECOVERABLE_ERROR:
                return "Recoverable Error";
                break;
            default:
                return "Unknown error ($errno)";
        }
    }
}
