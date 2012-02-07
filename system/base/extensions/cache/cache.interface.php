<?php
/**
 * Weegbo ICache interface file.
 *
 * Interface for classes supporting caching feature.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions.cache
 * @since 0.8
 */
interface ICache {

    /**
     * Check is key exist
     *
     * @access public
     * @param string $key
     */
    public function check($key);

    /**
     * Return data by key
     *
     * @access public
     * @param string $key
     */
    public function get($key);

    /**
     * Save data in cache
     *
     * @access public
     * @param string $key cache key
     * @param string $expire lifetime
     * @param mixed $data
     */
    public function set($key, $expire, $data);

    /**
     * Delete data from cache
     *
     * @access public
     * @param string $key
     */
    public function delete($key);

    /**
     * Delete all data from cache
     *
     * @access public
     */
    public function flush();
}