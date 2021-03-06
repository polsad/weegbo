<?php
/**
 * Base class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class Base {
    private static $_http_codes = null;

    /**
     * Create application.
     *
     * @access public
     * @param string $config - config
     * @return void
     */
    public static function createWebApplication($config) {
        /**
         * Include config class and load application configuration.
         */
        require_once($config['path']['base'].'config.class.php');
        Config::load($config, 'array');
        Config::load(Config::get('config/config'));
        /**
         * Enable profiler
         */
        if (Config::get('profiler/level')) {
            require_once(Config::get('path/base').'profiler.class.php');
            Profiler::add("Config loaded");
        }
        /**
         * Include static classes 
         */
        require_once(Config::get('path/base').'registry.class.php');
        require_once(Config::get('path/base').'exception.class.php');
        require_once(Config::get('path/base').'error.class.php');
        require_once(Config::get('path/base').'loader.class.php');
        require_once(Config::get('path/base').'input.class.php');
        /**
         * Set errors handlers
         */
        Error::setErrorsHandlers();
        /**
         * Set Loader, Input, Config to Register.
         */
        Registry::set('config', Config::getInstance());
        Registry::set('load', Loader::getInstance());
        Registry::set('input', Input::getInstance());
        if (Config::get('profiler/level')) {
            Profiler::add("Static framework classes loaded");
        }
        /**
         * Application routing.
         */
        self::routing();
        /**
         * Show profiler data
         */
        if (Config::get('profiler/level')) {
            Profiler::showResult();
        }
    }

    /**
     * Create controller and execute method base on URL.
     *
     * @access public
     * @return void
     */
    public static function routing() {
        $data = array(
            'get' => &$_GET,
            'post' => &$_POST,
            'cookie' => &$_COOKIE,
            'files' => &$_FILES,
            'uri' => array(),
            'host' => array(),
            'scheme' => 'http'
        );
        /**
         * Parse url
         */
        self::parseUrl($data);
        /**
         * Detect controller name
         */
        $cname = isset($data['uri'][0]) ? strtolower(trim($data['uri'][0])) : Config::get('app/default/controller');
        $cname = empty($cname) ? Config::get('app/default/controller') : $cname;
        /**
         * Detect action
         */
        $action = isset($data['uri'][1]) ? strtolower(trim($data['uri'][1])) : '';
        $action = ($action == null) ? Config::get('app/default/method') : $action;
        $action = ($action == null) ? 'index' : $action;
        $data['uri'] = array_slice($data['uri'], 2);
        /**
         * Init input data
         */
        Input::initData($data);
        /**
         * Autoload components
         */
        self::autoloadComponents();
        /**
         * Load controller
         */
        Loader::controller($cname, 'controller');
        /**
         * Execute controller method, this method must use only in base.class.php
         */
        Registry::get('controller')->execute($action);
    }

    /**
     * Send header with HTTP code
     */
    public static function sendHttpCode($code) {
        if (self::$_http_codes === null) {
            if (null === self::$_http_codes && file_exists(Config::get('path/config').'http-codes.php')) {
                self::$_http_codes = require_once(Config::get('path/config').'http-codes.php');
            }
        }
        if (array_key_exists($code, self::$_http_codes)) {
            Header("HTTP/1.1 {$code} ".self::$_http_codes[$code]);
        }
    }

    /**
     * URL parsing
     *
     * @access private
     * @param array link on result array
     * @return string
     */
    private static function parseUrl(&$data) {
        $data['scheme'] = (isset($_SERVER['HTTPS'])) ? 'https' : 'http';
        $buff = "{$data['scheme']}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $buff = parse_url($buff);
        // If parse_url return false
        if ($buff == false) {
            $buff = self::manualParseUrl();
        }
        $data['host'] = explode('.', $buff['host']);
        $buff['path'] = ltrim($buff['path'], '/');
        if (Config::get('app/router')) {
            $buff['path'] = self::replaceUrl($buff['path']);
        }
        $data['uri'] = explode('/', $buff['path']);
    }

    /**
     * URL parsing, if  parse_url returned false
     *
     * @access private
     * @return string
     */
    private static function manualParseUrl() {
        $res = $matches = array();
        preg_match_all("/([^.]+)\.?/", $_SERVER['HTTP_HOST'], $matches);
        $res['host'] = $matches[1];
        $res['path'] = $_SERVER['REQUEST_URI'];
        $pos = strpos($res['path'], '?');
        $res['path'] = ($pos === false) ? $res['path'] : substr($res, 0, $pos);
        return $res;
    }

    /**
     * The method replaces the original URL, in accordance with the regular expression.
     *
     * @access private
     * @param string $url original URL
     * @return string
     */
    private static function replaceUrl($url) {
        $routes = require(Config::get('config/router'));
        foreach ($routes as $k => $v) {
            $pattern = '/^'.$k.'[\/]?$/';
            if (preg_match($pattern, $url)) {
                $url = preg_replace($pattern, $v, $url);
                return $url;
            }
        }
        return $url;
    }

    /**
     * Autoload components
     */
    private static final function autoloadComponents() {
        /**
         * Load base components
         */
        $autoload = Config::get('app/autoload/', true);
        $autoload = Config::convertToArray($autoload, 'app/autoload/');

        $components = array(
            'base' => 'base',
            'extensions' => 'extension',
            'models' => 'model'
        );
        foreach ($components as $k => $v) {
            if (true === array_key_exists($k, $autoload)) {
                foreach ($autoload[$k] as $l => $m) {
                    if (is_array($m)) {
                        foreach ($m as $alias => $params) {
                            $vals = (is_int($alias)) ? array($l, $params) : array($l, $alias, $params);
							call_user_func_array("Loader::$v", $vals);
                        }
                    }
                    else {
                        $vals = array($l, $m);
                        call_user_func_array("Loader::$v", $vals);
                    }
                }
            }
        }
    }
}
