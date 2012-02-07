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
    private $_ch = NULL;
    private $_response_info = NULL;
    private $_response_body = NULL;

    public function __construct() {
    }

    public function setAuth($username, $password) {
        if ($username != '' && $password != '') {
            curl_setopt($this->_ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            curl_setopt($this->_ch, CURLOPT_USERPWD, $username.':'.$password);
        }
    }

    public function sendRequest($url, $method, $data = NULL) {
        /**
         * Set URL
         */
        $this->_ch = curl_init();
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_ch, CURLOPT_URL, $url);

        $method = strtoupper($method);
        try {
            /**
             * Set URL
             */
            switch ($method) {
                case 'GET':
                    $this->sendGetRequest();
                    break;
                case 'POST':
                    $this->sendPostRequest($data);
                    break;
                case 'PUT':
                    $this->sendPutRequest($data);
                    break;
                case 'DELETE':
                    $this->sendDeleteRequest();
                    break;
                default:
                    throw new Exception("Current methos ($method) is an invalid REST method.");
            }
        }
        catch (Exception $e) {
            curl_close($this->_ch);
            throw $e;
        }
    }

    public function getRequestMethod() {
        $result = NULL;
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



    private function sendGetRequest() {
        $this->executeRequest();
    }

    private function sendPostRequest($data) {
        $data = $this->buildPostData($data);
        if (NULL != $data) {
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($this->_ch, CURLOPT_POST, 1);
        }
        $this->executeRequest();
    }

    private function sendPutRequest($data) {
        curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $this->sendPostRequest($data);
    }

    private function sendDeleteRequest() {
        curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->executeRequest();
    }

    private function buildPostData($data) {
        $data = (is_array($data) && $data != NULL) ? http_build_query($data, '', '&') : NULL;
        return $data;
    }

    private function executeRequest() {
        $this->_response_body = curl_exec($this->_ch);
        $this->_response_info = curl_getinfo($this->_ch);
        curl_close($this->_ch);
    }


}