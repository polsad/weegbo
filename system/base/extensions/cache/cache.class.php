<?php
/**
 * Weegbo CacheExtension class file.
 *
 * Class for caching data. Support APC, filecache, eAccelerator, memcache
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions.cache
 * @since 0.8
 */
class CacheExtension {
    /**
     * @var string cache system
     */
    private $_cache = NULL;

    /**
     * Constructor, trying set cache system
     *
     * @access public
     * @param string $cache cache system
     * @return void
     */
    public function __construct($cache = NULL) {
        if (NULL == $cache) {
            $this->init(Config::get('app/cache/driver'));
        }
        else {
            $this->init($cache);
        }
    }

    /**
     * Check is key exist
     *
     * @access public
     * @param string $key
     * @return bool
     */
    public function check($key) {
        $key = $this->generateKey($key);
        return $this->_cache->check($key);
    }

    /**
     * Return data by key
     *
     * @access public
     * @param string $key
     * @return mixed
     */
    public function get($key) {
        $key = $this->generateKey($key);
        return $this->_cache->get($key);
    }

    /**
     * Save data in cache
     *
     * @access public
     * @param string $key cache key
     * @param string $expire lifetime
     * @param mixed $data
     * @return void
     */
    public function set($key, $expire, $data) {
        $key = $this->generateKey($key);
        $this->_cache->set($key, $expire, $data);
    }

    /**
     * Delete data from cache
     *
     * @access public
     * @param string $key
     * @return void
     */
    public function delete($key) {
        $key = $this->generateKey($key);
        $this->_cache->delete($key);
    }

    /**
     * Delete all data from cache
     *
     * @access public
     * @return void
     */
    public function flush() {
        $this->_cache->flush();
    }

    /**
     * Init cache system
     *
     * @access public
     * @param string $cache 'apc', 'file', 'eaccelerator', 'memcache'
     * @return void
     */
    private function init($cache) {
        $path = "cache/".strtolower($cache).'.class.php';
        if (file_exists(Config::get('path/extensions').$path)) {
            require_once(Config::get('path/extensions').$path);
            try {
                $class = ucfirst($cache).'Cache';
                $this->_cache = new $class;
            }
            catch (CException $e) {
                Error::exceptionHandler($e);
            }
            catch (Exception $e) {
                throw new CException("Cache class {$class} in file {$path} not found", 0, 500);
            }
        }
        else {
            throw new CException("Cache file {$path} not found", 0, 500);
        }
    }

    /**
     * Generate key
     *
     * @param string $key
     * @return string
     */
    private function generateKey($key) {
        return md5($key);
    }
}
