<?php
/**
 * Weegbo MemcacheCache class file.
 *
 * MemcacheCache provides Memcache caching.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions.cache
 * @since 0.8
 */
require_once('cache.interface.php');
class MemcacheCache implements ICache {
    private $_server = null;

    public function __construct($config) {
        if (!extension_loaded('memcache')) {
            throw new CException("MemcacheCache requires PHP memcache extension to be loaded", 500);
        }
        if (false === is_array($config['server']) && sizeof($config['server']) == 0) {
            throw new CException("Can't find server configuration in config", 500);
        }
        $this->_server = new Memcache;
        foreach ($config['server'] as $v) {
            $host = (isset($v['host']) && $v['host'] != '') ? $v['host'] : 'localhost';
            $port = (isset($v['port']) && $v['port'] != '') ? $v['port'] : ini_get('memcache.default_port');
            $this->_server->addServer($host, $port);
        }
    }

    public function check($key) {
        $result = $this->_server->get($key);
        $result = ($data == null) ? false : true;
        return $result;
    }

    public function get($key) {
        $data = $this->_server->get($key);
        return $data;
    }

    public function set($key, $expire, $data) {
        $this->_server->set($key, $data, MEMCACHE_COMPRESSED, $expire);
    }

    public function delete($key) {
        /**
         * For fix memcache delete bug, see http://php.net/manual/en/memcache.delete.php
         */
        $this->_server->delete($key, 0);
    }

    public function flush() {
        $this->_server->flush();
    }
}