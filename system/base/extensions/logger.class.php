<?php
/**
 * Weegbo LoggerExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions
 * @since 0.8
 */
class LoggerExtension {
    /**
     * @var string path to log file
     */
    private $_file = 'log.txt';
    /**
     * @var resource log file resource
     */
    private $_resource = NULL;
    /**
     * @var string date format
     */
    private $_date_format = 'Y-m-d H:i:s';

    /**
     * Open log file.
     *
     * @access public
     * @return void
     */
    public function __construct($log_path) {
        $this->_file = $log_path;
        $this->_resource = fopen($this->_file, 'a+');
    }

    /**
     * Close log file.
     *
     * @access public
     * @return void
     */
    public function __destruct() {
        if (NULL != $this->_resource) {
            fclose($this->_resource);
        }
    }

    /**
     * Add message to log file.
     *
     * @access public
     * @param string $message message
     * @return void
     */
    public function setMessage($message) {
        if ($this->_resource) {
            $message = '['.date($this->_date_format).'] '.$message."\n";
            flock($this->_resource, LOCK_EX);
            fwrite($this->_resource, $message);
            flock($this->_resource, LOCK_UN);
        }
    }

    /**
     * Set new log file.
     *
     * @access public
     * @param string $log_path path to log file
     * @return void
     */
    public function setLogFile($log_path) {
        $this->_file = $log_path;
        if (NULL != $this->_resource) {
            fclose($this->_resource);
        }
        if (file_exists($this->_file)) {
            $this->_resource = fopen($this->_file, 'a+');
        }
        else {
            $this->_resource = NULL;
        }
    }
}
