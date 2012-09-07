<?php
/**
 * Config class file.
 *
 * Config singleton class for work with app config
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class Config {
    /**
     * @var static property to hold singleton instance
     */
    private static $_instance = null;

    /**
     *
     * @var static property for save configuration data
     */
    private static $_config = array();
    private static $_separator = '/';

    /**
     * Load application config from file.
     *
     * @access public
     * @param string $file configuration filename
     * @return void
     */
    public static function load($config, $type = 'file') {
        switch ($type) {
            case 'array':
                $aliases = array();
                self::_setAliases((array) $config, $aliases);
                self::$_config = array_merge_recursive(self::$_config, $aliases);
                break;
            default:
                if (file_exists($config)) {
                    $config = require($config);
                    $aliases = array();
                    self::_setAliases($config, $aliases);
                    self::$_config = array_merge_recursive(self::$_config, $aliases);
                }
        }
    }

    /**
     * Returned value by config key
     *
     * @access public
     * @param string $key
     * @param bool $part
     * @return mixed
     */
    public static function get($key, $part = false) {
        if ($part === false) {
            $result = isset(self::$_config[$key]) ? self::$_config[$key] : null;
        }
        else {
            $result = null;
            $key = rtrim($key, self::$_separator).self::$_separator;
            foreach (self::$_config as $k => $v) {
                if (0 === strpos($k, $key)) {
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }

    /**
     * Setup new value for key. If the key does not exist - create it.
     *
     * @access public
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value) {
        if (is_array($value)) {
            $aliases = array();
            self::_setAliases($value, $aliases, $key);
            self::$_config = array_merge_recursive(self::$_config, $aliases);
        }
        else {
            self::$_config[$key] = $value;
        }
    }
    
    public function setSeparator($separator) {
        self::$_separator = $separator;
    }

    /**
     * Convert aliases to array
     *
     * @access public
     * @param array $aliases
     * @param string $prefix - if prefix !== null, delete it from aliases
     * @return array
     */
    public static function convertToArray($aliases, $prefix = null) {
        $result = array();
        $lenght = ($prefix === null) ? 0 : strlen($prefix);
        foreach ((array) $aliases as $k => $v) {
            $keys = ($prefix === null) ? $k : substr($k, $lenght);
            $keys = explode(self::$_separator, $keys);
            $vals = &$result;
            foreach ($keys as $key) {
                if (!isset($vals[$key])) {
                    $vals[$key] = array();
                }
                $vals = &$vals[$key];
            }
            $vals = $v;
        }
        return $result;
    }

    /**
     * Factory method to return the singleton instance.
     *
     * @access public
     * @return object
     */
    public static function getInstance() {
        if (null == Config::$_instance) {
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
     * Method converts a multidimensional array to a one-dimensional array.
     * Example: array('path' => array('config' => ..., 'tmpls' => ...))
     * to array('path/config' => ... 'path/tmpls' => ...).
     *
     * @access private
     * @param array $config configuration array
     * @param array $aliases link on alias array
     * @param string $path
     * @return void
     */
    private static function _setAliases($config, &$aliases, $path = null) {
        foreach ($config as $k => $v) {
            if (is_array($v)) {
                self::_setAliases($v, $aliases, $path.$k.self::$_separator);
            }
            else {
                $aliases[$path.$k] = $v;
            }
        }
    }
}
