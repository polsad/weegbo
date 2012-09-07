<?php
/**
 * Error class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class Error {

    public static function setErrorsHandlers() {
        // If debug/level <= 2 - hide errors
        if (Config::get('debug/level') <= 2) {
            error_reporting(0);
            set_error_handler(array('Error', 'errorHandler'), Config::get('debug/error-level'));
            register_shutdown_function(array('Error', 'fatalErrorHandler'));
        }
        else {
            error_reporting(Config::get('debug/error-level'));
        }
        set_exception_handler(array('Error', 'exceptionHandler'));
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline) {
        Error::exceptionHandler(new ErrorException($errstr, $errno, 0, $errfile, $errline));
    }

    public static function fatalErrorHandler() {
        $error = error_get_last();
        if (($error['type'] === E_ERROR) || ($error['type'] === E_USER_ERROR)) {
            Error::exceptionHandler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
        }
        else {
            return true;
        }
    }

    /**
     * Display or write to log exception error.
     *
     * @access public
     * @param object $e exception
     * @return void
     */
    public static function exceptionHandler($e) {
        $class = get_class($e);
        $code = ($class == 'CException') ? $e->getStatusCode() : 500;
        if ($code == 500) {
            $error = "Error: {$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}";
            switch (Config::get('debug/level')) {
                // Level 0 - don't display errors, write error to default log file
                case 0:
                    error_log("Error: {$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
                    break;
                // Level 1 - don't display errors, write error to debug/debug-logfile file
                case 1:
                    error_log("[".date('D M j G:i:s Y')."] {$error}\n", 3, Config::get('debug/file'));
                    break;
                // Level 2 - don't display errors, send error to debug/email
                case 2:
                    $host = (isset($_SERVER['HTTPS'])) ? 'https' : 'http';
                    $host = "{$host}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
                    error_log("Host: {$host}\nDate: ".date('D M j G:i:s Y')."\nUser agent: {$_SERVER['HTTP_USER_AGENT']}\n{$error}\n", 1, Config::get('debug/email'));
                    break;
            }
        }
        // Show errors on display
        if (Config::get('debug/level') == 3) {
            echo '<pre>'; print_r($e); echo '</pre>';
        }
        else {
            if (false == Registry::isValid('view')) {
                Loader::view();
            }
            // Send header with error code
            Base::sendHttpCode($code);
            Registry::get('view')->display('errors/error-'.$code.'.tpl');
            exit();
        }
    }
}