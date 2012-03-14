<?php
/**
 * This file contains config details.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
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
     *    app/autoload/base - основные компоненты (db, view)
     *    app/autoload/extensions - расширения
     *    app/autoload/models - модели
     * app/ob-gzip - enable/disable gzip compression
     * app/session-name - session name
     */
    'app' => array(
        'default-controller' => 'main',
        'router' => true,
        'cache' => array(
            'driver' => 'file',
            'server' => array(
                array('host' => '', 'port' => '')
            )
        ),
        'ob-gzip' => false,
        'session-name' => 'session',
    ),
    /**
     * Debug options
     * debug/debug - enable or disable debug (Load errors, Db errors, etc...)
     * debug/statistic - enable or disable statistics collection
     * debug/log - full path to debug log file
     */
    'debug' => array(
        'debug' => true,
        'profiler' => true,
        'profiler-trace' => true,
        'log' => Config::get('path/app').'error.log'
    )
);