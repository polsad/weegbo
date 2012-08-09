<?php
/**
 * Weegbo CacheExtension class file.
 *
 * Class for caching data. Support APC, filecache, eAccelerator, memcache
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions.cache
 * @since 0.8
 */
class CacheExtension {
    /**
     * @var string cache system
     */
    private $_cache = null;

    /**
     * Constructor, trying set cache system
     *
     * @access public
     * @param string $cache cache system
     * @return void
     */
    public function __construct($config = null) {
        if (null === $config) {
            throw new CException("Unknown cache driver. ", 500);
        }
        if (false === isset($config['driver'])) {
            throw new CException("Can't find driver in config. ", 500);
        }
        $path = "cache/".strtolower($config['driver']).'.class.php';
        if (file_exists(Config::get('path/extensions').$path)) {
            require_once(Config::get('path/extensions').$path);
            try {
                $class = ucfirst($config['driver']).'Cache';
                // Delete driver from config
                unset($config['driver']);
                $this->_cache = new $class($config);
            }
            catch (CException $e) {
                Error::exceptionHandler($e);
            }
            catch (Exception $e) {
                throw new CException("Cache class {$class} in file {$path} not found", 500);
            }
        }
        else {
            throw new CException("Cache file {$path} not found", 500);
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
     * Generate key
     *
     * @param string $key
     * @return string
     */
    private function generateKey($key) {
        return md5($key);
    }
}