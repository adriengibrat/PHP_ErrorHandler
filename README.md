# PHP Logger
Is full featured PHP erorr handler With web interface.

# Simple Setup
```php
<?php
/* Include the Error Handler class file */
require_once('PHPlogger/PHPlogger.php');

// The first parameter, error_reporting level. (See http://php.net/manual/en/function.error-reporting.php).
// The second parameter, Full path to a directory where you like to store all error log file. Please dont forget to chmod to 777 to the log directory.
// The third parameter, this determines whether errors should be printed to the screen as part of the output or if they should be hidden from the user.
// The fourth parameter, if set to true, Send outputs to ChromePhp or firePHP for Firebug
$error = new PHPlogger(-1, 'full/path/to/log/dir', true, true);

echo $i_do_not_exits;

```

# Changes
###0.01:
First version Released.


# Dependencies for PHP Logger
ChromePHP (http://www.chromephp.com)

FirePHP (http://www.firephp.org)

Note: dependencies are included with this package.

# Questions?
If you have any questions, please feel free to ask on the http://k1ngdom.net


# License
Copyright &copy; 2013, Steven King: http://k1ngdom.net

Released under the MIT License.
