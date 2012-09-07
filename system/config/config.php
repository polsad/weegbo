<?php
/**
 * This file contains config details.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.config
 * @since 0.8
 */
return array(
    /**
     * Application options
     * app/default-controller - default controller
     * app/router - enable or disable router for replace URL
     * app/cache - configuration for cache system
     *    app/cache/driver - application cache driver (apc, eaccelerator, file, memcache)
     *    app/cache/server - configuration for memcache servers. 
     *    app/cache/server/host - option only for memcache system. Memcache server.
     *    app/cache/server/port - option only for memcache system. Memcache port.
     * app/autoload - компоненты которые будут загруженны автоматически
     *    app/autoload/base - основные компоненты (view)
     *    app/autoload/extensions - расширения
     *    app/autoload/models - модели
     * app/ob-gzip - enable/disable gzip compression
     * app/session-name - session name
     */
    'app' => array(
        'default-controller' => 'main',
        'router' => true,
        'autoload' => array(
            'base' => array(
                'view' => 'view'
            ),
            'extensions' => array(
                'db' => array(
                    'db' => 'master'
                ),
                'cache' => array(
                    'cache' => array(
                        'driver' => 'file',
                        'server' => array('host' => '', 'port' => '')
                    )
                )
            ),
            'models' => array(
                'main' => 'main'
            )
        ),
        'ob-gzip' => false,
        'session-name' => 'session',
    ),
    /**
     * Debug options
     * debug/level - debug level
     *    0 - all errors and exceptions hide, all logging by default // Production level
     *    1 - all errors and exceptions hide, all errors writing to 'debug/file' file
     *    2 - all errors and exceptions hide, all errors sending to 'debug/email'
     *    3 - all errors show, error_reporting(debug/error-level)
     * debug/file - full path to debug log file
     * debug/email - email for error messages
     */
    'debug' => array(
        'level' => 3,
        'file' => Config::get('path/app').'error.log',
        'email' => 'polsad@gmail.com',
        'error-level' => E_ALL ^ E_NOTICE
    ),
    /**
     * Profiler option
     * profiler/level - profiler level
     *    0 - disable
     *    1 - show statistic
     *    2 - show statistic and trace
     */
    'profiler' => array(
        'level' => 2
    )
);