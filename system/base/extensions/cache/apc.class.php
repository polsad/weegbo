<?php
/**
 * Weegbo ApcCache class file.
 *
 * ApcCache provides APC caching.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions.cache
 * @since 0.8
 */
require_once('cache.interface.php');
class ApcCache implements ICache {

    public function __construct() {
        if (!extension_loaded('apc')) {
            throw new CException('ApcCache requires PHP apc extension to be loaded', 6, 500);
        }
    }

    public function check($key) {
        $result = false;
        /**
         * apc_exists support apc >= 3.1.4
         */
        if (function_exists('apc_exists')) {
            $result = apc_exists($key);
        }
        else {
            $result = apc_fetch($key);
        }
        return $result;
    }

    public function get($key) {
        return apc_fetch($key);
    }

    public function set($key, $expire, $data) {
        apc_store($key, $data, $expire);
    }

    public function delete($key) {
        apc_delete($key);
    }

    public function flush() {
        apc_clear_cache('user');
    }
}
