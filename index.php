<?php
/**
 * Weegbo bootstrap file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
/**
 * Define basic constant
 *
 * PATH_ROOT - path to application directory
 * PATH_BASE - path to framework base files
 */
define('START_TIME', microtime(true));
define('PATH_ROOT', dirname(__file__).'/');
/**
 * File system paths
 * path/config - path to directory with config files
 * path/cache - path to directiry with cache files
 * path/libs - path to directiry with libraries
 * path/controllers - path to directiry with controllers
 * path/models - path to directiry with models
 * path/extensions - path to directiry with framework extensions
 * path/helpers - path to directiry with templates
 * path/tmpls - path to directiry with templates
 * path/host - root URL (for example: http://site.com/) 
 */
$config = array(
    'path' => array(
        'app' => PATH_ROOT.'system/',
        'base' => PATH_ROOT.'system/base/',
        'host' => 'http://'.$_SERVER['HTTP_HOST'].'/'
    )
);
$paths = array(
    'path' => array(
        'config' => $config['path']['app'].'config/',
        'cache' => $config['path']['app'].'cache/',
        'libs' => $config['path']['app'].'libraries/',
        'controllers' => $config['path']['app'].'controllers/',
        'models' => $config['path']['app'].'models/',
        'extensions' => $config['path']['base'].'extensions/',
        'helpers' => $config['path']['base'].'helpers/',
        'tmpls' =>  $config['path']['app'].'tmpls/'
    )
);
$config = array_merge_recursive($config, $paths);

/**
 * Run application. PATH_ROOT.'system/' - path to application folder
 */
require_once($config['path']['base'].'base.class.php');
Base::createWebApplication($config);