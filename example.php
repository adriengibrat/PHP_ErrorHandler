<?php

/* Include the Error Handler class file */
require_once('ErrorHandler/ErrorHandler.php');

/* Initiate the ErrorHandler */

// The first parameter, error_reporting level. (See http://php.net/manual/en/function.error-reporting.php).
// The second parameter, Full path to a directory where you like to store all error log file. Please dont forget to chmod to 777 to the log directory.
// The third parameter, this determines whether errors should be printed to the screen as part of the output or if they should be hidden from the user.
// The fourth parameter, if set to true, Send outputs to ChromePhp or firePHP for Firebug
$error = new ErrorHandler(-1, './log', true, true);

echo $i_do_not_exits;
