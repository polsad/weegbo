<?php
/**
 * Config class file.
 *
 * Config это singleton class для работы с конфигурацией приложения.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class Config {
    /**
     * @var static property to hold singleton instance
     */
    private static $_instance = NULL;
    /**
     *
     * @var static property for save configuration data
     */
    private static $_config = array();

    /**
     * Load application config from file.
     *
     * @access public
     * @param string $file configuration filename
     * @return void
     */
    public static function load($config, $type = 'file') {
        switch($type) {
            case 'array':
                $aliases = array();
                self::setAliases((array) $config, $aliases);
                self::$_config = array_merge(self::$_config, $aliases);             
            break;
            default:
                if (file_exists(self::$_config['path/config']."{$config}.php")) {
                    $config = require_once(self::$_config['path/config']."{$config}.php");
                    $aliases = array();
                    self::setAliases($config, $aliases);
                    self::$_config = array_merge(self::$_config, $aliases);
                }          
        }
    }

    /**
     * Возвращает значение по ключу.
     *
     * @access public
     * @param string $key
     * @return mixed
     */
    public static function get($key, $part = false) {
        if ($part == false) {
            $result = isset(self::$_config[$key]) ? self::$_config[$key] : NULL;
        }
        else {
            $result = array();
            foreach(self::$_config as $k => $v) {
                if (0 === strpos($k, $key)) {
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }

    /**
     * Устанавливает новое значение. Если ключ не существует - создает его.
     *
     * @access public
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value) {
        if (is_array($value)) {
            $aliases = array();
            self::setAliases($value, $aliases, $key);
            self::$_config = array_merge(self::$_config, $aliases);
        }
        else {
            self::$_config[$key] = $value;
        }
    }

    /**
     * Factory method to return the singleton instance.
     *
     * @access public
     * @return object
     */
    public static function getInstance() {
        if (NULL == Config::$_instance) {
            Config::$_instance = new Config;
        }
        return Config::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        
    }

    /**
     * Метод преобразует многомерный массив в одномерный массив соответсвий.
     * Example: array('path' => array('config' => ..., 'tmpls' => ...))
     * преобразуется в array('path/config' => ... 'path/tmpls' => ...).
     *
     * @access private
     * @param array $config configuration array
     * @param array $aliases link on alias array
     * @param string $path
     * @return void
     */
    private static function setAliases($config, &$aliases, $path = NULL) {
        foreach ($config as $k => $v) {
            if (is_array($v)) {
                self::setAliases($v, $aliases, $path . $k . '/');
            }
            else {
                $aliases[$path . $k] = $v;
            }
        }
    }
}
