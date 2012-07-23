<?php
/**
 * Input class file.
 *
 * Input это singleton class для работы с входными данными приложения.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class Input {
    /**
     *
     * @var static property to hold singleton instance
     */
    private static $_instance = null;

    /**
     *
     * @var static property to store input data
     */
    private static $_data = array();

    /**
     * Init input data
     *
     * @access public
     * @param array $data init input data
     * @return void
     */
    public static function initData($data) {
        self::$_data = $data;
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $this->_stripSlashes();
        }
    }

    /**
     * Check data
     *
     * @access public
     * @param string $key
     * @param mixed $true result in success
     * @param mixed $false result in unsuccess
     * @param string $index get, post, cookie, domain or uri
     * @return mixed
     */
    public static function checkData($key, $true, $false, $index) {
        $value = isset(self::$_data[$index][$key]) ? $true : $false;
        return $value;
    }

    /**
     * Check GET data
     *
     * @access public
     * @param string $key
     * @param mixed $true result in success
     * @param mixed $false result in unsuccess
     * @return mixed
     */
    public static function checkGet($key, $true = true, $false = false) {
        return self::checkData($key, $true, $false, 'get');
    }

    /**
     * Check POST data
     *
     * @access public
     * @param string $key
     * @param mixed $true result in success
     * @param mixed $false result in unsuccess
     * @return mixed
     */
    public static function checkPost($key, $true = true, $false = false) {
        return self::checkData($key, $true, $false, 'post');
    }

    /**
     * Check COOKIE data
     *
     * @access public
     * @param string $key
     * @param mixed $true result in success
     * @param mixed $false result in unsuccess
     * @return mixed
     */
    public static function checkCookie($key, $true = true, $false = false) {
        return self::checkData($key, $true, $false, 'cookie');
    }

    /**
     * Check FILES data
     *
     * @access public
     * @param string $key
     * @param mixed $true result in success
     * @param mixed $false result in unsuccess
     * @return mixed
     */
    public static function checkFiles($key, $true = true, $false = false) {
        return self::checkData($key, $true, $false, 'files');
    }

    /**
     * Check URI data
     *
     * @access public
     * @param string $key
     * @param mixed $true result in success
     * @param mixed $false result in unsuccess
     * @return mixed
     */
    public static function checkUri($key, $true = true, $false = false) {
        return self::checkData($key, $true, $false, 'uri');
    }

    /**
     * Check DOMAIN data
     *
     * @access public
     * @param string $key
     * @param mixed $true result in success
     * @param mixed $false result in unsuccess
     * @return mixed
     */
    public static function checkDomain($key, $true = true, $false = false) {
        return self::checkData($key, $true, $false, 'domain');
    }

    /**
     * Return data
     *
     * @access public
     * @param string $key
     * @param string $type type 'int', 'string', 'float', 'bool', 'array'
     * @param mixed $default default result
     * @param string $index get, post, cookie, domain or uri
     * @return mixed
     */
    public static function getData($key, $type, $default, $index) {
        $value = isset(self::$_data[$index][$key]) ? self::$_data[$index][$key] : $default;
        if ($value != $default) {
            settype($value, $type);
            if ($value == $default) {
                $value = $default;
            }
        }
        return $value;
    }

    /**
     * Return GET data
     *
     * @access public
     * @param string $key
     * @param string $type type 'int', 'string', 'float', 'bool', 'array'. 'string' by default
     * @param mixed $default default result, null by default
     * @return mixed
     */
    public static function get($key, $type = 'string', $default = null) {
        return self::getData($key, $type, $default, 'get');
    }

    /**
     * Return POST data
     *
     * @access public
     * @param string $key
     * @param string $type type 'int', 'string', 'float', 'bool', 'array'. 'string' by default
     * @param mixed $default default result, null by default
     * @return mixed
     */
    public static function post($key, $type = 'string', $default = null) {
        return self::getData($key, $type, $default, 'post');
    }

    /**
     * Return COOKIE data
     *
     * @access public
     * @param string $key
     * @param string $type type 'int', 'string', 'float', 'bool', 'array'. 'string' by default
     * @param mixed $default default result, null by default
     * @return mixed
     */
    public static function cookie($key, $type = 'string', $default = null) {
        return self::getData($key, $type, $default, 'cookie');
    }

    /**
     * Return FILES data
     *
     * @access public
     * @param string $key
     * @param string $type type 'array'.
     * @param mixed $default default result, null by default
     * @return mixed
     */
    public static function files($key, $type = 'array', $default = null) {
        return self::getData($key, $type, $default, 'files');
    }

    /**
     * Return URI data
     *
     * @access public
     * @param string $key
     * @param string $type type 'int', 'string', 'float', 'bool', 'array'. 'string' by default
     * @param mixed $default default result, null by default
     * @return mixed
     */
    public static function uri($key, $type = 'string', $default = null) {
        return self::getData($key, $type, $default, 'uri');
    }

    /**
     * Return DOMAIN data
     *
     * @access public
     * @param string $key
     * @param string $type type 'int', 'string', 'float', 'bool', 'array'. 'string' by default
     * @param mixed $default default result, null by default
     * @return mixed
     */
    public static function domain($key, $type = 'string', $default = null) {
        return self::getData($key, $type, $default, 'domain');
    }

    /**
     * Set new data
     *
     * @access public
     * @param string $key
     * @param mixed $value
     * @param string $index get, post, cookie, domain or uri
     * @return void
     */
    public static function setData($key, $value, $index) {
        if (isset(self::$_data[$index])) {
            self::$_data[$index][$key] = $value;
        }
    }

    /**
     * Factory method to return the singleton instance.
     *
     * @access public
     * @return object
     */
    public static function getInstance() {
        if (null == Input::$_instance) {
            Input::$_instance = new Input;
        }
        return Input::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        
    }

    /**
     * Stripslashes
     */
    private function _stripSlashes() {
        self::$_data['get'] = array_map('stripslashes', self::$_data['get']);
        self::$_data['post'] = array_map('stripslashes', self::$_data['post']);
        self::$_data['cookie'] = array_map('stripslashes', self::$_data['cookie']);
    }
}