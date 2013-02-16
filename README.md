# PHP ErrorHandler
Is a full featured PHP erorr handler With web interface.

# Simple Setup
```php
/* Include the Error Handler class file */
require_once('ErrorHandler/ErrorHandler.php');

// Initiate the PHP ErrorHandler and  set error_reporting level. (See http://php.net/manual/en/function.error-reporting.php).
$ErrorHandler = new ErrorHandler(E_ALL);

// Set a full path to a file where you like to store log. set to boolean false to disable log to file.
$ErrorHandler->LogFile('./log/error.log'); // Logging enabled

// Display errors on web page. I do not recommend this to be turned on a production site.
$ErrorHandler->DisplayError(true); // Enabled.

// Set to false to disable. to enable simply write your project name.
$ErrorHandler->SysLogIdent('MyErrorHandler'); // Enabled.

// Display errors on Browser's Console. (Plugin for browsers are required).
$ErrorHandler->LogToConsole(true); // Enabled

```
See example.php for more information.


# Changes
###0.01:
First version Released. Web interface will be released soon

###0.02:
- Some bug fixes.
- New Features:
 - Catch Fatal Errors
 - Catch Exception
 - Syslog
 - Templating system


# TODO
- PHP auto_prepend_file script for Parse Error
- An ajax enabled web interface for log viewing.


# Dependencies for PHP ErrorHandler
- ChromePHP (http://www.chromephp.com)
- FirePHP (http://www.firephp.org)

# NOTE
- ErrorHander automatically disable xdebug
- Cannot catch "Parse Error"
- Dependencies are included with this package.
- Plugins required for browser for LogToConsole();
 - ChromePHP: http://www.chromephp.com
 - FirePHP: http://www.firephp.org


# Questions?
If you have any questions, please feel free to ask on the http://k1ngdom.net


# License
Copyright &copy; 2013, Steven King: http://k1ngdom.net

Released under the MIT License.