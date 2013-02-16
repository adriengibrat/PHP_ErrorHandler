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
 * @package    PHP logger
 * @author     Steven King <info@k1ngdom.net>
 * @copyright  2012 Steven King.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    0.01
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
 *
 * @package PHPlogger
 * @author Steven King <info@k1ngdom.net>
 */
class ErrorHandler {
    /**
     * @var array
     */
    private $settings = array();


    /**
     * The Constructor.
     * @param int     $ErrorLevel   error_reporting level. (See http://php.net/manual/en/function.error-reporting.php).
     * @param string  $LogDir       Full path to a directory where you like to store all error log file.
     * @param boolean $DisplayError This determines whether errors should be printed to the screen as part of the output or if they should be hidden from the user.
     * @param boolean $LogToConsole Send outputs to ChromePhp or firePHP for Firebug
     */
    public function __construct($ErrorLevel = false, $LogDir = false, $DisplayError = false, $LogToConsole = false) {
        $this->setting['ErrorLevel']   = $ErrorLevel ? $ErrorLevel : ini_get('error_reporting');
        $this->setting['LogDir']       = $LogDir;
        $this->setting['DisplayError'] = $DisplayError;
        $this->setting['LogToConsole'] = $LogToConsole;

        /* Default settings. */
        $this->setting['DateFormat']   = 'd-m-Y H:i:s';

        set_error_handler(array($this, 'HandleErrors'), $this->setting['ErrorLevel']);
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

        /* Remove documment root from path. */
        $errfile = '.'. str_replace(array(DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']), array('/', ''), $errfile);

        /* Display Errors */
        if ($this->setting['DisplayError'] !== false) {
            $this->DisplayError($errno, $errstr, $errfile, $errline);
        }

        /* Write Errors to file. */
        if ($this->setting['LogDir'] !== false) {
            $this->LogToFile($errno, $errstr, $errfile, $errline);
        }

        /* Send Errors to firePHP and chromePhp */
        if ($this->setting['LogToConsole'] !== false) {
            $this->LogToFirePHP($errno, $errstr, $errfile, $errline);
        }


        /* Don't execute PHP internal error handler */
        return true;
    }


    /**
     * Revert to the previous error handler (which could be the built-in or a user defined function).
     */
    public function Destroy() {
        restore_error_handler();
    }


    /**
     * Reinitiate PHPLogger after calling the Destroy(). This maybe will be handy in future.
     */
    public function Reinitiate() {
        set_error_handler(array($this, 'HandleErrors'), $this->setting['ErrorLevel']);
    }


    /**
     * Writes errors to an error log file.
     *
     * @param string $errno      The level of the error raised.
     * @param string $errstr     The error message.
     * @param string $errfile    The filename that the error was raised in.
     * @param int    $errline    The line number the error was raised at.
     */
    private function LogToFile($errno, $errstr, $errfile, $errline) {
        $ErrorLog = date($this->setting['DateFormat']) . " " . $this->ErrorFriendlyName($errno) . ": ".  $errstr  ." in " . $errfile . " on line " . $errline . "\r\n";
        $LogFile = $this->setting['LogDir'] . '/error.log';
        file_put_contents($LogFile, $ErrorLog, FILE_APPEND|LOCK_EX);
    }


    /**
     * Display errors on web page. I do not recommend this to be turned on on production site.
     *
     * @param string $errno      The level of the error raised.
     * @param string $errstr     The error message.
     * @param string $errfile    The filename that the error was raised in.
     * @param int    $errline    The line number the error was raised at.
     */
    private function DisplayError($errno, $errstr, $errfile, $errline) {
        echo "<br /><b>" . $this->ErrorFriendlyName($errno) . "</b>: ".  $errstr  ." in <b>" . $errfile . "</b> on line <em>" . $errline. "</em><br />\r\n";
    }


    /**
     * Display errors on Browser's Console. (Plugin for browsers are required).
     *
     * @param string $errno      The level of the error raised.
     * @param string $errstr     The error message.
     * @param string $errfile    The filename that the error was raised in.
     * @param int    $errline    The line number the error was raised at.
     */
    private function LogToFirePHP($errno, $errstr, $errfile, $errline) {
        /** Check if Header is already sent. **/
        if (headers_sent() === true) {
            return false;
        }

        switch($errno) {
            case E_ERROR:
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

        $ErrorLog = $this->ErrorFriendlyName($errno) . ": ".  $errstr  ." in " . $errfile . " on line " . $errline;

        if (class_exists('FB') === true) {
            call_user_func(array('FB', $LogType), $ErrorLog);
        }

        if (class_exists('ChromePhp') === true) {
            $LogType = str_replace('::info', '::log', $LogType);
            call_user_func(array('ChromePhp', $LogType), $ErrorLog);
        }

        return true;
    }



    /**
     * Set custom settings
     *
     * @param string $name  Name of the setting.
     * @param string $value Value of the setting.
     */
    public function SetSetting($name, $value) {
        $this->setting[$name] = $value;
        return true;
    }


    /**
     * Replace integer with human readable name.
     *
     * @param int $errno The level of the error raised.
     */
    private function ErrorFriendlyName ($errno) {
        switch($errno) {
            case E_ERROR:
                return "Error";
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
