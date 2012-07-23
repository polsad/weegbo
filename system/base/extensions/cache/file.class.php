<?php
/**
 * Weegbo FileCache class file.
 *
 * FileCache provides file caching.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions.cache
 * @since 0.8
 */
require_once('cache.interface.php');
class FileCache implements ICache {

    public function check($key) {
        $check = false;
        if (file_exists(Config::get('path/cache').$key.'.php')) {
            if (@filemtime(Config::get('path/cache').$key.'.php') > time()) {
                $check = true;
            }
        }
        return $check;
    }

    public function get($key) {
        $data = null;
        if (file_exists(Config::get('path/cache').$key.'.php')) {
            if (@filemtime(Config::get('path/cache').$key.'.php') > time()) {
                $data = file_get_contents(Config::get('path/cache').$key.'.php');
                $data = unserialize($data);
            }
            else {
                @unlink(Config::get('path/cache').$key.'.php');
            }
        }
        return $data;
    }

    public function set($key, $expire, $data) {
        $expire = ($expire <= 0) ? 2592000 : $expire;
        $expire = time() + $expire;
        $data = serialize($data);
        if (@file_put_contents(Config::get('path/cache').$key.'.php', $data, LOCK_EX) == strlen($data)) {
            @chmod(Config::get('path/cache').$key.'.php', 0777);
            @touch(Config::get('path/cache').$key.'.php', $expire);
        }
    }

    public function delete($key) {
        if (file_exists(Config::get('path/cache').$key.'.php')) {
            @unlink(Config::get('path/cache').$key.'.php');
        }
    }

    public function flush() {
        $handle = opendir(Config::get('path/cache'));
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    @unlink(Config::get('path/cache').$file);
                }
            }
            closedir($handle);
        }
    }
}