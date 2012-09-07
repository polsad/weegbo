<?php
/**
 * This file contains database config details.
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
     * Define database(s) constants
     * database/xxxxx - name of server
     * database/xxxxx/driver - database driver, supported PDO
     * database/xxxxx/host - database hostname
     * database/xxxxx/user - database user
     * database/xxxxx/pass - database password
     * database/xxxxx/db - database name
     * database/xxxxx/prefix - table prefix
     * database/xxxxx/charset - database character
     */
    'database' => array(
        'master' => array(
            'driver' => 'mysql',
            'host' => 'localhost',
            'user' => 'root',
            'pass' => '',
            'db' => '',
            'prefix' => 'prfx_',
            'charset' => 'utf8'
        )
    )
);