<?php
/**
 * Weegbo EacceleratorCache class file.
 *
 * EacceleratorCache provides EAccelerator caching.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions.cache
 * @since 0.8
 */
require_once('cache.interface.php');
class EacceleratorCache implements ICache {

    public function __construct() {
        if (!extension_loaded('eaccelerator_get')) {
            throw new CException('EacceleratorCache requires PHP eaccelerator extension to be loaded', 6, 500);
        }
    }

    public function check($key) {
        $result = eaccelerator_get($key);
        $result = ($result == NULL) ? false : true;
        return $result;
    }

    public function get($key) {
        return eaccelerator_get($key);
    }

    public function set($key, $expire, $data) {
        eaccelerator_put($key, $data, $expire);
    }

    public function delete($key) {
        eaccelerator_rm($key);
    }

    public function flush() {
        eaccelerator_clean();
    }
}