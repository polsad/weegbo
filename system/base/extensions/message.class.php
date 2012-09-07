<?php
/**
 * Weegbo MessageExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions
 * @since 0.8
 */
class MessageExtension {
    /**
     * @var array messages
     */
    private $_messages = array();

    /**
     * Construct.
     *
     * @access public
     * @param string $block file with messages
     * @return void
     */
    public function __construct($config) {
        $this->setConfig($config);
    }

    public function setConfig($config) {
        if ($config != null) {
            $isArray = is_array($config);
            $messages = array();
            if ($isArray === false && file_exists(Config::get('path/messages').$config)) {
                $messages = require(Config::get('path/messages').$config);
            }
            if ($isArray === true) {
                $messages = $config;
            }
            $aliases = array();
            $this->setAliases($messages, $aliases);
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
        $message = (isset($this->_messages[$key])) ? $this->_messages[$key] : null;
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
