<?php
/**
 * Weegbo RestExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions
 * @since 0.8
 */
class RestExtension {
    private $_ch = null;
    private $_config = array(
        'user' => '',
        'password' => '',
        'ssl' => false,
        'timeout' => 10,
        'headers' => array()
    );
    private $_response_info = null;
    private $_response_body = null;

    public function __construct($config = null) {
        if (!extension_loaded('curl')) {
            throw new CException('Rest requires PHP curl extension to be loaded', 500);
        }
        $this->setConfig($config);
    }

    public function setConfig($config) {
        if ($config !== null && is_array($config) == true) {
            foreach ($this->_config as $k => $v) {
                $this->_config[$k] = (array_key_exists($k, $config) === false) ? $v : $config[$k];
            }
        }
    }

    public function getConfig() {
        return $this->_config;
    }
    
    public function sendRequest($url, $method, $data = null) {
        /**
         * Set URL
         */
        $this->_initConfig();
        $method = strtoupper($method);
        /**
         * Set URL
         */
        switch ($method) {
            case 'GET':
                $this->_sendGetRequest($url, $data);
                break;
            case 'POST':
                $this->_sendPostRequest($url, $data);
                break;
            case 'PUT':
                $this->_sendPutRequest($url, $data);
                break;
            case 'DELETE':
                $this->_sendDeleteRequest($url);
                break;
            default:
                curl_close($this->_ch);
                throw new CException("Current methos ($method) is an invalid REST method.");
        }
    }

    public function getRequestMethod() {
        $result = null;
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $methods = array('GET', 'POST', 'PUT', 'DELETE');
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            if (in_array($method, $methods)) {
                $result = $method;
            }
        }
        return $result;
    }

    public function getRequestPutData() {
        $result = array();
        $putdata = file_get_contents('php://input');
        $putdata = explode('&', $putdata);
        foreach ($putdata as $pair) {
            $item = explode('=', $pair);
            if (count($item) == 2) {
                $result[urldecode($item[0])] = urldecode($item[1]);
            }
        }
        return $result;
    }

    public function getResponseInfo() {
        return $this->_response_info;
    }

    public function getResponseBody() {
        return $this->_response_body;
    }

    public function checkAuth() {
        $user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
        $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
        $result = ($user == $this->_config['user'] && $password == $this->_config['password']) ? true : false;
        return $result;
    }

    private function _initConfig() {
        $this->_ch = curl_init();
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, $this->_config['timeout']);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->_config['user'] != '' || $this->_config['password'] != '') {
            curl_setopt($this->_ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->_ch, CURLOPT_USERPWD, $this->_config['user'].':'.$this->_config['password']);
        }
        if ($this->_config['ssl'] === true) {
            curl_setopt($this->_ch, CURLOPT_VERBOSE, 0);
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        if ($this->_config['headers'] != null) {
            curl_setopt($this->_ch, CURLOPT_HTTPHEADER, (array) $this->_config['headers']);
        }
    }

    private function _sendGetRequest($url, $data) {
        $data = $this->_buildPostData($data);
        if (null != $data) {
            $data = (strpos('?', $url) !== false) ? '?'.$data : '&'.$data;
            $url .= $data;
        }
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        $this->_executeRequest();
    }

    private function _sendPostRequest($url, $data) {
        $data = $this->_buildPostData($data);
        if (null != $data) {
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($this->_ch, CURLOPT_POST, 1);
        }
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        $this->_executeRequest();
    }

    private function _sendPutRequest($url, $data) {
        curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $this->_sendPostRequest($url, $data);
    }

    private function _sendDeleteRequest($url) {
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->_executeRequest();
    }

    private function _buildPostData($data) {
        $data = (is_array($data) && $data != null) ? http_build_query($data, '', '&') : null;
        return $data;
    }

    private function _executeRequest() {
        $this->_response_body = curl_exec($this->_ch);
        $this->_response_info = curl_getinfo($this->_ch);
        curl_close($this->_ch);
    }
}