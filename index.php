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
 * path/app - path to directiry with application
 * path/base - path to directiry with framework
 * path/host - root URL (for example: http://site.com/) 
 *
 */
$config = array(
    'path' => array(
        'app' => PATH_ROOT.'system/',
        'base' => PATH_ROOT.'system/base/',
        'host' => 'http://'.$_SERVER['HTTP_HOST'].'/'
    )
);
/**
 * System paths
 * path/cache - path to directiry with cache files 
 * path/config - path to directory with config files
 * path/controllers - path to directiry with controllers 
 * path/extensions - path to directiry with framework extensions 
 * path/helpers - path to directiry with templates 
 * path/libs - path to directiry with libraries
 * path/models - path to directiry with models
 * path/tmpls - path to directiry with templates
 */
$config = array_merge_recursive($config,
    array(
        'path' => array(
            'cache' => $config['path']['app'].'cache/',
            'config' => $config['path']['app'].'config/',        
            'controllers' => $config['path']['app'].'controllers/',
            'extensions' => $config['path']['base'].'extensions/',
            'helpers' => $config['path']['base'].'helpers/',
            'libs' => $config['path']['app'].'libraries/',
            'models' => $config['path']['app'].'models/',
            'tmpls' =>  $config['path']['app'].'tmpls/'
        )
    )
);
/**
 * System paths
 * config/config - file for config
 * config/database - file for database
 * config/router - file for router
 */
$config = array_merge_recursive($config,
    array(
        'config' => array(
            'config' => $config['path']['config'].'config.php',
            'router' => $config['path']['config'].'routes.php',        
            'database' => $config['path']['config'].'database.php'
        )
    )
);
/**
 * Run application. PATH_ROOT.'system/' - path to application folder
 */
require_once($config['path']['base'].'base.class.php');
Base::createWebApplication($config);