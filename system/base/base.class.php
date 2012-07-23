<?php
/**
 * Base class file.
 *
 * Base класс подключает необходимые для работы классы (Config, Error, Exception,
 * Register и Loader), реализует функции роутера и подключает необходимый контроллер,
 * выводит статистику по работе приложения (время работы, потребляемая память,
 * количество запросов к БД, общая прододжительность запросов).
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
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
        if (Config::get('debug/profiler')) {
            require_once(Config::get('path/base').'profiler.class.php');
            Profiler::add("Config loaded");
        }
        /**
         * Include static classes exception and error, set error handler Error::codeError method.
         */
        require_once(Config::get('path/base').'exception.class.php');
        require_once(Config::get('path/base').'error.class.php');
        set_error_handler(create_function('$c, $m, $f, $l', 'Error::errorHandler($m, $c, $f, $l);'), E_ALL ^ E_NOTICE);
        /**
         * Include class Registry, Loader and Input
         */
        require_once(Config::get('path/base').'registry.class.php');
        require_once(Config::get('path/base').'loader.class.php');
        require_once(Config::get('path/base').'input.class.php');
        /**
         * Set Loader, Input, Config to Register.
         */
        Registry::set('config', Config::getInstance());
        Registry::set('load', Loader::getInstance());
        Registry::set('input', Input::getInstance());
        if (Config::get('debug/profiler')) {
            Profiler::add("Static framework classes loaded");
        }
        /**
         * Application routing.
         */
        try {
            self::routing();
        }
        catch (CException $e) {
            Error::exceptionHandler($e);
        }
        /**
         * Show profiler data
         */
        if (Config::get('debug/profiler')) {
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
            'domain' => array()
        );

        self::parseDomain($_SERVER['HTTP_HOST'], $data['domain']);
        self::parseUrl(substr($_SERVER['REQUEST_URI'], 1), $data['uri']);

        /**
         * Detect controller name
         */
        $cname = isset($data['uri'][0]) ? strtolower(trim($data['uri'][0])) : Config::get('app/default-controller');
        $cname = empty($cname) ? Config::get('app/default-controller') : $cname;
        /**
         * Detect action
         */
        $action = isset($data['uri'][1]) ? strtolower(trim($data['uri'][1])) : '';
        $action = empty($action) ? 'index' : $action;
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

    public function sendHttpCode($code) {
        if ($this->_http_codes === null) {
            if (null === $this->_http_codes && file_exists(Config::get('path/config').'http-codes.php')) {
                $this->_http_codes = require_once(Config::get('path/config').'http-codes.php');
            }
        }
        if (array_key_exists($code, $this->_http_codes)) {
            Header("HTTP/1.1 {$code} {$this->_http_codes[$code]}");
        }
    }

    /**
     * Parse URL for obtaining domain and subdomains.
     *
     * @param string $url
     * @param link $domain array with domain/subdomain segments
     * @return void
     */
    private function parseDomain($url, &$domain) {
        preg_match_all("/([^.]+)\.?/", $url, $matches);
        $domain = $matches[1];
    }

    /**
     * Parse URL on URI and GET segments.
     *
     * @access private
     * @param string $url
     * @param link $uri array with URI segments
     * @param link $get array with GET segment
     * @return void
     */
    private static function parseUrl($url, &$uri) {
        $uri = strpos($url, '?');
        $uri = ($uri === false) ? $url : substr($url, 0, $uri);
        if (Config::get('app/router')) {
            $uri = self::replaceUrl($uri);
        }
        $uri = explode('/', $uri);
    }

    /**
     * Метод заменяет исходный URL в соответствии с регулярным выражением.
     *
     * @access private
     * @param array $urls хэш с шаблонами и на что они будут заменяться
     * @param string $url исходный URL по которому будет проводиться поиск
     * @return string
     */
    private static function replaceUrl($url) {
        $routes = require_once(Config::get('config/router'));
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
     * Автозагрузка компонентов
     */
    private final function autoloadComponents() {
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
                    $vals = is_array($m) ? (is_int($l)) ? $m : array_merge(array($l), $m)  : (array) $m;
                    call_user_func_array("Loader::$v", $vals);
                }
            }
        }
    }
}
