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
     * @var int trace index
     */
    protected $_traceIndex = 1;

    /**
     * @var int HTTP status code
     */
    protected $_statusCode = 500;

    public function __construct($message, $traceIndex = null, $statusCode = null) {
        $this->_traceIndex = ($traceIndex === null) ? $this->_traceIndex : $traceIndex;
        $this->_statusCode = ($statusCode === null) ? $this->_statusCode : $statusCode;
        parent::__construct($message, null);
    }

    /**
     * @access public
     * @return int HTTP status code
     */
    public function getStatusCode() {
        return $this->_statusCode;
    }

    /**
     * @access public
     * @return int trace index
     */
    public function getTraceIndex() {
        return $this->_traceIndex;
    }
}