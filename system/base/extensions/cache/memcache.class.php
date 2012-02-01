<?php
/**
 * Weegbo MemcacheCache class file.
 *
 * MemcacheCache provides Memcache caching.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions.cache
 * @since 0.8
 */
require_once('cache.interface.php');
class MemcacheCache implements ICache {
    private $_server = NULL;

    public function __construct() {
        if (!extension_loaded('memcache_add_server')) {
            throw new CException("MemcacheCache requires PHP memcache extension to be loaded", 6, 500);
        }
        else {
            if (!is_array(Config::get('app/cache/server'))) {
                throw new CException("Can't load server configuration", 6, 500);
            }
            $this->_server = new Memcache;
            foreach ((array) Config::get('app/cache/server') as $server) {
                $host = ($server['host'] == '') ? 'localhost' : $server['host'];
                $port = ($server['port'] == '') ? ini_get('memcache.default_port') : $server['port'];
                $this->_server->addServer($host, $port);
            }
        }
    }

    public function check($key) {
        $result = $this->_server->get($key);
        $result = ($data == NULL) ? false : true;
        return $result;
    }

    public function get($key) {
        $data = $this->_server->get($key);
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
