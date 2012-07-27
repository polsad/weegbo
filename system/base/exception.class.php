<?php
/**
 * Exception class file.
 *
 * CException class - common class for exception handling.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class CException extends Exception {
    /**
     * @var int HTTP status code
     */
    protected $_httpCode = 500;

    public function __construct($message, $httpCode = null) {
        $this->_httpCode = ($httpCode === null) ? $this->_httpCode : $httpCode;
        parent::__construct($message);
    }

    /**
     * @access public
     * @return int HTTP status code
     */
    public function getStatusCode() {
        return $this->_httpCode;
    }
}