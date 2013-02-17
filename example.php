<?php

/* Include the Error Handler class file */
require_once('ErrorHandler/ErrorHandler.php');


// Initiate the PHP ErrorHandler and set error_reporting level. (See http://php.net/manual/en/function.error-reporting.php).
$ErrorHandler = new ErrorHandler(E_ALL);

// Set a full path to a file where you like to store log. set to boolean false to disable log to file.
$ErrorHandler->LogFile('./log/error.log'); // Logging enabled

// Display errors on web page. I do not recommend this to be turned on a production site.
$ErrorHandler->DisplayError(true); // Enabled.

// Set to false to disable. to enable simply write your project name.
$ErrorHandler->SysLogIdent('MyErrorHandler'); // Enabled.

// Display errors on Browser's Console. (Plugin for browsers are required).
$ErrorHandler->LogToConsole(true); // Enabled

/** NOTE: By default all above configurations are set to false. **/


// Fatal error handler. its enabled by default. set to false to disable.
// NOTE: This function cannot catch "Parse error"
// $ErrorHandler->CatchFatal(false);


// Disable the Errorhandler.
// $ErrorHandler->Destroy();


// Reinitiate the ErrorHandler. Only required if you want to initiate after calling Destroy();
// $ErrorHandler->Reinitiate();


// Date format. Default: d-m-Y H:i:s (See http://php.net/manual/en/function.date.php)
$ErrorHandler->DateFormat('d-m-Y H:i:s');


/** Test Begins **/

function a ($b, $c) {
    echo "test";
}


echo $i_do_not_exits; // Notice

echo a(); // Warning

throw new Exception('this is a test'); // Uncaught Exception

echo fatal(); // Fatal Error.

echo $iwontbeexecuted; // Notice
