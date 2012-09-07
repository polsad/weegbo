<?php
/**
 * Weegbo LoggerExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions
 * @since 0.8
 */
class LoggerExtension {
    /**
     * @var resource log file resource
     */
    private $_file = null;
    private $_config = array(
        'file' => 'log.txt',
        'date-format' => 'D M j G:i:s Y'
    );

    /**
     * Open log file.
     *
     * @access public
     * @return void
     */
    public function __construct($config = null) {
        $this->setConfig($config);
        $this->init();
    }

    /**
     * Close log file.
     *
     * @access public
     * @return void
     */
    public function __destruct() {
        $this->close();
    }

    public function init() {
        $this->close();
        $this->_file = fopen($this->_config['file'], 'a+');
    }

    public function setConfig($config) {
        if ($config !== null && is_array($config) == true) {
            foreach ($this->_config as $k => $v) {
                $this->_config[$k] = (array_key_exists($k, $config) === false) ? $v : $config[$k];
            }
        }
    }

    public function close() {
        if (null !== $this->_file) {
            fclose($this->_file);
        }
    }

    /**
     * Add message to log file.
     *
     * @access public
     * @param string $message message
     * @return void
     */
    public function add($message) {
        if (null !== $this->_file) {
            $data = date($this->_config['date-format']);
            flock($this->_file, LOCK_EX);
            fwrite($this->_file, "[{$data}] {$message}\n");
            flock($this->_file, LOCK_UN);
        }
    }
}