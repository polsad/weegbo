<?php
/**
 * Weegbo CryptExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.extensions
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
/**
 * CryptExtension class
 *
 * Class for crypt data, used mcrypt PHP extension.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.extensions
 * @since 0.8
 */
class CryptExtension {
    private $_td = NULL;
    private $_iv = NULL;
    private $_key = NULL;
    private $_method = NULL;


    public function  __construct($key, $method = MCRYPT_DES) {
        $this->init($key, $method);
    }

    public function init($key, $method = MCRYPT_DES) {
        $this->_key = $key;
        $this->_method = $method;
    }

    public function encrypt($source) {
        $this->_td = mcrypt_module_open($this->_method, '', 'ofb', '');
        $this->_iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->_td), MCRYPT_RAND);
        
        $source = serialize($source);
        $ksize = mcrypt_enc_get_key_size($this->_td);
        $key = substr($this->_key, 0, $ksize);
        mcrypt_generic_init($this->_td, $key, $this->_iv);

        $result = array(
            'iv' => base64_encode($this->_iv),
            'data' => base64_encode(mcrypt_generic($this->_td, $source))
        );

        $this->close();
        return $result;
    }

    public function decrypt($source) {
        $source['iv'] = base64_decode($source['iv']);
        $source['data'] = base64_decode($source['data']);

        $this->_td = mcrypt_module_open($this->_method, '', 'ofb', '');
        $this->_iv = $source['iv'];

        $ksize = mcrypt_enc_get_key_size($this->_td);
        $key = substr($this->_key, 0, $ksize);
        mcrypt_generic_init($this->_td, $key, $this->_iv);
        
        $result = mdecrypt_generic($this->_td, $source['data']);
        $result = unserialize($result);

        $this->close();
        return $result;
    }

    private function close() {
        mcrypt_generic_deinit($this->_td);
        mcrypt_module_close($this->_td);
    }
}