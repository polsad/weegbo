<?php
/**
 * Error class file.
 *
 * Если debug включен, то ошибки выводятся на экран.
 * Если debug выключен, то ошибки логгируются и выводиться страница ошибки
 * (обычно уже на production server). В этом случае используется расширение
 * logger, которое загрузиться автоматически.
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
        if (Config::get('debug/level') <=2) {
            error_reporting(0);
            set_error_handler(array('Error', 'errorHandler'), Config::get('debug/error-level'));
            register_shutdown_function(array('Error', 'fatalErrorHandler'));
        }
        else {
            error_reporting(Config::get('debug/error-level'));
        }
        set_exception_handler(array('Error' ,'exceptionHandler'));
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
        // Если status code == 500, это значит что произошла ошибка
        // либо было выброшено исключение CException со статус кодом 500 (т.е. ошибка загрузки и т.д.)
        if ($code == 500) {
            $error = "Error: {$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}";
            // В зависимости от уровня debug формируем и логгируем ошибки
            switch (Config::get('debug/level')) {
                // Уровень 0 - ошибки не отображаются логгируем ошибки по умолчанию
                case 0:
                    error_log("Error: {$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
                break;            
                // Уровень 1 - ошибки не отображаются, все записывается в файл debug/debug-logfile
                case 1:
                    error_log("[".date('D M j G:i:s Y')."] {$error}\n", 3, Config::get('debug/file'));
                break;
                // Уровень 2 - ошибки не отображаются, сообщение с ошибками отсылается на debug/email
                case 2:
                    $host = (isset($_SERVER['HTTPS'])) ? 'https' : 'http';
                    $host = "{$host}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
                    error_log("Host: {$host}\nDate: ".date('D M j G:i:s Y')."\nUser agent: {$_SERVER['HTTP_USER_AGENT']}\n{$error}\n", 1, Config::get('debug/email'));
                break;
            }            
        }
        if (Config::get('debug/level') == 3) {
            echo '<pre>'; print_r($e); echo '</pre>';
        }
        else {
            if (false == Registry::isValid('view')) {
                Loader::view();
            }
            // Отправляем заголовок с кодом ошибки
            Base::sendHttpCode($code);            
            Registry::get('view')->display('errors/error-'.$code.'.tpl');
            exit();
        }
    }
}