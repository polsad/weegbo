<?php
/**
 * Weegbo MessageHelper class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.helpers
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
require_once(PATH_BASE.'helper.class.php');
/**
 * MessageHelper class
 *
 * Helper for output message in templater
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.helpers
 * @since 0.8
 */
class MessageHelper extends Helper {
    /**
     * @var array helper arguments
     */
    private $_name = NULL;

    public function __construct($name) {
        $this->_name = Registry::isValid($name) ? $name : '';
    }

    public function execute($args = array()) {
        $key = isset($args[0]) ? $args[0] : NULL;
        return ($this->_name == NULL || $key == NULL) ? NULL : Registry::get($this->_name)->get($key);
    }
}
