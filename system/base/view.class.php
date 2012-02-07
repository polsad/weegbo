<?php
/**
 * View class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class View {
    /**
     * @var array storage template's variables
     */
    private $_data = array();
    /**
     * @var array storage template's helper objects
     */
    private $_helpers = array();

    /**
     * Return value from $data by name
     *
     * @access public
     * @param string $name object's name in $data
     * @return mixed
     */
    public function __get($name) {
        return $this->_data[$name];
    }

    /**
     * Execute helper $name method execute().
     * If helper not load, load this helper. If helper not exist, display error.
     *
     * @access public
     * @param string $name helper's name
     * @param array $args helper's arguments
     * @return mixed
     */
    public function __call($name, $args) {
        $helper = strtolower($name);
        if (!isset($this->_helpers[$helper])) {
            /**
             * Load helper
             */
            Loader::helper($name, $name);
            return $this->_helpers[$helper]->execute($args);
        }
        else {
            return $this->_helpers[$helper]->execute($args);
        }
    }

    /**
     * Assigns values to template variables.
     *
     * @access public
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function assign($name, $value) {
        $this->_data[$name] = $value;
    }

    public function helper($helper, $obj) {
        $this->_helpers[$helper] = $obj;
    }

    /**
     * Executes and returns the template results as string.
     *
     * @access public
     * @param string $tmpl template
     * @param int $expire [option] cache live time in seconds
     * @param string $cache [option] cache filename
     * @return string template as string
     */
    public function fetch($tmpl, $expire = NULL, $cache = NULL) {
        $result = '';
        if (NULL != $expire) {
            if (NULL == $cache) {
                $cache = $this->getCacheKey($tmpl);
            }
            $result = $this->getCache($cache);
            if (NULL == $result) {
                if ($this->checkFileTemplate($tmpl)) {
                    ob_start();
                    extract($this->_data);
                    require(Config::get('path/tmpls').$tmpl);
                    $result = ob_get_contents();
                    ob_end_clean();
                }
                $this->setCache($cache, $expire, $result);
            }
        }
        else {
            if ($this->checkFileTemplate($tmpl)) {
                ob_start();
                extract($this->_data);
                require(Config::get('path/tmpls').$tmpl);
                $result = ob_get_contents();
                ob_end_clean();
            }
        }
        return $result;
    }

    /**
     * Include and executes template.
     *
     * @access public
     * @param string $templete template
     * @param int $cache_time [option] cache live time in seconds
     * @return void
     */
    public function template($template, $cache_time = NULL) {
        echo $this->fetch($template, $cache_time);
    }

    /**
     * Executes and display the template results.
     *
     * @access public
     * @param string $templete template
     * @param int $cache_time [option] cache live time in seconds
     * @return void
     */
    public function display($templete, $cache_time = NULL) {
        if (Config::get('ob_gzip') == true) {
            ob_start('ob_gzhandler', 9);
        }
        $this->template($templete, $cache_time);
    }

    /**
     * Checks whether requested template exists.
     *
     * @param string $templete template
     * @return bool
     */
    private function checkFileTemplate($templete) {
        if (!file_exists(Config::get('path/tmpls').$templete)) {
            throw new CException("Template file {$templete} not found", 2);
        }
        return true;
    }

    /**
     * Retutn cache filename by template filename
     *
     * @param string $template template filename
     * @return string
     */
    private function getCacheKey($template) {
        $cache_file = 'tmpl-'.md5($template).'.tpl';
        return $cache_file;
    }

    /**
     * If cache file exist and cache is live, return file content
     *
     * @access private
     * @param string $file
     * @return mixed
     */
    private function getCache($file) {
        $result = NULL;
        if (file_exists(Config::get('path/cache').$file)) {
            if (@filemtime(Config::get('path/cache').$file) > time()) {
                $result = file_get_contents(Config::get('path/cache').$file);
            }
            else {
                @unlink(Config::get('path/cache').$file);
            }
        }
        return $result;
    }

    /**
     * Save data in cache
     *
     * @access public
     * @param string $template template filename
     * @param string $content data
     * @param string $file [option] cache filename
     * @return void
     */
    private function setCache($file, $expire, $content) {
        $expire = ($expire <= 0) ? 2592000 : $expire;
        $expire = time() + $expire;
        if (@file_put_contents(Config::get('path/cache').$file, $content, LOCK_EX)) {
            @chmod(Config::get('path/cache').$file, 0777);
            @touch(Config::get('path/cache').$file, $expire);
        }
    }
}