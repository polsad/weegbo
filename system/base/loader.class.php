<?php
/**
 * Loader class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class Loader {
    /**
     * @var static property to hold singleton instance
     */
    private static $_instance = NULL;

    /**
     * Static method for loading controller
     *
     * @access public
     * @param string $controller controller name
     * @return void
     */
    public static function controller() {
        $args = func_get_args();
        $controller = isset($args[0]) ? strtolower($args[0]) : NULL;
        $name = isset($args[1]) ? $args[1] : $model;
        $args = array_slice($args, 2);

        if (file_exists(Config::get('path/controllers').$controller.'.class.php')) {
            require_once(Config::get('path/controllers').$controller.'.class.php');
            $class = ucwords($controller).'Controller';
            try {
                self::createObject($class, $name, $args);
                if (Config::get('debug/profiler')) {
                    Profiler::add("Controller class {$class} loaded", 'load-controller');
                }
            }
            catch (CException $e) {
                Error::exceptionHandler($e);
            }
            catch (Exception $e) {
                throw new CException("Controller class {$class} in file {$controller}.class.php not found", 1, 404);
            }
        }
        else {
            throw new CException("Controller file {$controller}.php not found", 1, 404);
        }
    }

    /**
     * Static method for loading extension
     *
     * @access public
     * @param string $extension extension class name (for example 'Validator')
     * @param string $name      extension name (for example 'validator')
     * @param mixed [option]
     * @return void
     */
    public static function extension() {
        $args = func_get_args();
        $extension = isset($args[0]) ? strtolower($args[0]) : NULL;
        $name = isset($args[1]) ? $args[1] : $extension;
        $args = array_slice($args, 2);

        if (file_exists(Config::get('path/extensions').$extension.'.class.php')) {
            require_once(Config::get('path/extensions').$extension.'.class.php');
        }
        elseif (file_exists(Config::get('path/extensions').$extension.'/'.$extension.'.class.php')) {
            require_once(Config::get('path/extensions').$extension.'/'.$extension.'.class.php');
        }
        else {
            throw new CException("Extension file {$extension}.class.php not found", 0, 500);
        }

        $class = ucfirst($extension).'Extension';
        try {
            self::createObject($class, $name, $args);
            if (Config::get('debug/profiler')) {
                Profiler::add("Extension class {$class} loaded", 'load-extension');
            }
        }
        catch (CException $e) {
            Error::exceptionHandler($e);
        }
        catch (Exception $e) {
            throw new CException("Extension class {$class} in file {$extension}.class.php not found", 0, 500);
        }
    }
    
    /**
     * Static method for loading helper
     *
     * @access public
     * @param string $helper helper class name (for example 'Message')
     * @param string $name helper name (for example 'message')
     * @param mixed [option]
     * @return void
     */
    public static function helper() {
        $args = func_get_args();
        $helper = isset($args[0]) ? strtolower($args[0]) : NULL;
        $name = isset($args[1]) ? $args[1] : $helper;
        $args = array_slice($args, 2);

        if (file_exists(Config::get('path/helpers').$helper.'.class.php')) {
            require_once(Config::get('path/helpers').$helper.'.class.php');
        }
        else {
            throw new CException("Helper file {$helper}.class.php not found", 2, 500);
        }
        $class = ucfirst($helper).'Helper';
        try {
            self::createObject($class, $name, $args, true);
            if (Config::get('debug/profiler')) {
                Profiler::add("Helper class {$class} loaded", 'load-helper');
            }
        }
        catch (CException $e) {
            Error::exceptionHandler($e);
        }
        catch (Exception $e) {
            throw new CException("Helper class {$class} in file {$helper}.class.php not found", 0, 500);
        }
    }

    /**
     * Static method for loading model
     *
     * @access public
     * @param string $model model class name (for example 'News')
     * @param string $name  model name (for example 'news')
     * @return void
     */
    public static function model() {
        $args = func_get_args();
        $model = isset($args[0]) ? strtolower($args[0]) : NULL;
        $name = isset($args[1]) ? $args[1] : $model;
        $args = array_slice($args, 2);
        if (file_exists(Config::get('path/models').$model.'.class.php')) {
            require_once(Config::get('path/models').$model.'.class.php');
            $class = ucfirst($model).'Model';
            try {
                self::createObject($class, $name, $args);
                if (Config::get('debug/profiler')) {
                    Profiler::add("Model class {$class} loaded", 'load-model');
                }
            }
            catch (CException $e) {
                Error::exceptionHandler($e);
            }
            catch (Exception $e) {
                throw new CException("Model class {$class} in file {$model}.class.php not found", 0, 500);
            }
        }
        else {
            throw new CException("Model file {$model}.class.php not found", 0, 500);
        }
    }

    /**
     * Static method for loading library
     *
     * @access public
     * @return void
     */
    public static function library() {
        $args = func_get_args();
        $path = isset($args[0]) ? $args[0] : NULL;
        $library = isset($args[1]) ? $args[1] : NULL;
        $name = isset($args[2]) ? $args[2] : NULL;
        $args = array_slice($args, 3);
        if (file_exists(Config::get('path/libs').$path)) {
            require_once(Config::get('path/libs').$path);
            try {
                self::createObject($library, $name, $args);
                if (Config::get('debug/profiler')) {
                    Profiler::add("Library class {$library} loaded", 'load-library');
                }
            }
            catch (CException $e) {
                Error::exceptionHandler($e);
            }
            catch (Exception $e) {
                throw new CException("Library class {$library} in file {$path} not found", 0, 500);
            }
        }
        else {
            throw new CException("Library file {$path} not found", 0, 500);
        }
    }

    /**
     * Load class for work with database
     * database.php - file with configuration
     *
     * @access public
     * @return void
     */
    public static function db() {
        $args = func_get_args();
        $server = isset($args[0]) ? $args[0] : NULL;
        /**
         * Check config
         */
        if (Config::get('database', true) == NULL) {
            Config::load(Config::get('config/database'));
            require_once(Config::get('path/base').'db.class.php');
        }
        /**
         * Check server config
         */
        if (Config::get('database/'.$server, true) == NULL) {
            throw new CException("DB config {$server} not found", 0, 500);
        }
        $db = Db::getInstance();
        $db->connect($server);
        Registry::set('db', $db);
    }

    /**
     * Static method for loading template engine
     *
     * @access public
     * @return void
     */
    public static function view() {
        require_once(Config::get('path/base').'view.class.php');
        Registry::set('view', new View);
    }

    /**
     * Factory method to return the singleton instance
     *
     * @access public
     * @return Registry
     */
    public static function getInstance() {
        if (NULL == Loader::$_instance) {
            Loader::$_instance = new Loader;
        }
        return Loader::$_instance;
    }

    public function __construct() {
        
    }

    /**
     * Return value from Registry by name
     *
     * @access public
     * @param string $name object's name in Registry
     * @return mixed
     */
    public function __get($name) {
        return Registry::get($name);
    }

    private static function createObject(&$class, &$name, &$args, $helper = false) {
        $obj = new ReflectionClass($class);
        /**
         * If object is not helper, register it in Registry
         */
        if ($helper === false) {
            Registry::set($name, call_user_func_array(array(&$obj, "newInstance"), $args));
        }
        /**
         * If object is helper, register it in View
         */
        else {
            if (!Registry::isValid('view')) {
                Loader::view();
            }
            Registry::get('view')->helper($name, call_user_func_array(array(&$obj, "newInstance"), $args));
        }
    }
}
