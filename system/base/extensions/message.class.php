<?php
/**
 * Weegbo MessageExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions
 * @since 0.8
 */
class MessageExtension {
    /**
     * @var array messages
     */
    private $_messages = array();
    private $_mainblock = null;

    /**
     * Construct.
     *
     * @access public
     * @param string $block file with messages
     * @return void
     */
    public function __construct($block) {
        $this->_mainblock = $block;
        $this->load($this->_mainblock);
    }

    public function load($block) {
        if (file_exists(Config::get('path/messages')."{$block}.php")) {
            $aliases = array();
            $messages = require_once(Config::get('path/messages')."{$block}.php");
            $this->setAliases($messages, $aliases, $block.'/');
            $this->_messages = array_merge($this->_messages, $aliases);
        }
    }

    /**
     * Get message by key
     *
     * @access public
     * @param string $key message key
     * @return message
     */
    public function get($key) {
        $message = null;
        $key = (strpos($key, '/') == false) ? $this->_mainblock.'/'.$key : $key;
        if (isset($this->_messages[$key])) {
            $message = $this->_messages[$key];
        }
        return $message;
    }

    private function setAliases($messages, &$aliases, $path = null) {
        foreach ($messages as $k => $v) {
            if (is_array($v)) {
                $this->setAliases($v, $aliases, $path.$k.'/');
            }
            else {
                $aliases[$path.$k] = $v;
            }
        }
    }
}
