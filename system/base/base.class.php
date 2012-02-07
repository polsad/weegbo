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
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class Base {
    /**
     * Create application.
     *
     * @access public
     * @param string $path_app - path to application folder
     * @return void
     */
    public static function createWebApplication($path_app) {
        /**
         * Define path application, PATH_ROOT and PATH_BASE defined in file index.php.
         */
        define('PATH_APP', $path_app);
        /**
         * Include config class and load application configuration.
         */
        require_once(PATH_BASE.'config.class.php');
        Config::load('config');
        /**
         * Enable profiler
         */
        if (Config::get('debug/profiler')) {
            require_once(PATH_BASE.'profiler.class.php'); 
        }
        /**
         * Include static classes exception and error, set error handler Error::codeError method.
         */
        require_once(PATH_BASE.'exception.class.php');
        require_once(PATH_BASE.'error.class.php');
        set_error_handler(create_function('$c, $m, $f, $l', 'Error::errorHandler($m, $c, $f, $l);'), E_ALL ^ E_NOTICE);
        /**
         * Include class Register.
         */
        require_once(PATH_BASE.'registry.class.php');
        /**
         * Include class Loader and Input, create loader, input and config instance.
         */
        require_once(PATH_BASE.'loader.class.php');
        require_once(PATH_BASE.'input.class.php');
        Registry::set('load', Loader::getInstance());
        Registry::set('input', Input::getInstance());
        Registry::set('config', Config::getInstance());

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
            'get' => array(),
            'post' => &$_POST,
            'cookie' => &$_COOKIE,
            'files' => &$_FILES,
            'uri' => array(),
            'domain' => array()
        );

        self::parseDomain($_SERVER['HTTP_HOST'], $data['domain']);
        self::parseUrl(substr($_SERVER['REQUEST_URI'], 1), $data['uri'], $data['get']);

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
    private static function parseUrl($url, &$uri, &$get) {
        if (strpos($url, '?') !== false) {
            $url = explode('?', $url);
            $uristr = $url[0];
            $getstr = $url[1];
        }
        else {
            $uristr = $url;
            $getstr = NULL;
        }
        if (Config::get('app/router')) {
            $uristr = self::replaceUrl($uristr);
        }
        $uri = explode('/', $uristr);
        if (NULL != $getstr) {
            $buff = explode('&', $getstr);
            $size = sizeof($buff);
            for ($i = 0; $i < $size; $i++) {
                $params = explode('=', $buff[$i]);
                $get[$params[0]] = $params[1];
            }
        }
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
        $routes = require_once(Config::get('path/config').'routes.php');
        foreach ($routes as $k => $v) {
            $pattern = '/^'.$k.'[\/]?$/';
            if (preg_match($pattern, $url)) {
                $url = preg_replace($pattern, $v, $url);
                return $url;
            }
        }
        return $url;
    }

    private final function autoloadComponents() {
        $autoload = require_once(Config::get('path/config').'autoload.php');
        /**
         * Load base components
         */
        foreach ($autoload['base'] as $k => $v) {
            if (is_array($v) && NULL != $v) {
                call_user_func_array("Loader::$k", $v);
            }
            else {
                Loader::$v();
            }
        }

        /**
         * Load extensions
         */
        foreach ($autoload['extensions'] as $k => $v) {
            if (is_array($v) && NULL != $v) {
                $vals = array($k);
                $vals = array_merge($vals, $v);
            }
            else {
                $vals = array($v);
            }
            call_user_func_array('Loader::extension', $vals);
        }
        /**
         * Load models
         */
        foreach ($autoload['models'] as $k => $v) {
            if (is_array($v) && NULL != $v) {
                $vals = array($k);
                $vals = array_merge($vals, $v);
            }
            else {
                $vals = array($v);
            }
            call_user_func_array('Loader::model', $vals);
        }
        /**
         * Set templates vars
         */
        Registry::get('view')->assign('path_host', Config::get('path/host'));
    }
}