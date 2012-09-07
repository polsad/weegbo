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
    private $_config = array(
        'td' => null,
        'iv' => null,
        'key' => null,
        'method' => MCRYPT_DES
    );

    /**
     * You can see methods on http://www.php.net/manual/ru/mcrypt.ciphers.php
     */
    public function __construct($config = null) {
        if (!extension_loaded('mcrypt')) {
            throw new CException('Crypt requires PHP mcrypt extension to be loaded', 500);
        }        
        $this->setConfig($config);
    }

    public function setConfig($config) {
        if (is_array($config) == true) {
            foreach ($this->_config as $k => $v) {
                $this->_config[$k] = (array_key_exists($k, $config) === false) ? $this->_config[$k] : $config[$k];
            }
        }
    }

    public function encrypt($source) {
        $this->_config['td'] = mcrypt_module_open($this->_config['method'], '', 'ofb', '');
        $this->_config['iv'] = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->_config['td']), MCRYPT_RAND);

        $source = serialize($source);
        $ksize = mcrypt_enc_get_key_size($this->_config['td']);
        $key = substr($this->_config['key'], 0, $ksize);
        mcrypt_generic_init($this->_config['td'], $key, $this->_config['iv']);

        $result = array(
            base64_encode($this->_config['iv']),
            base64_encode(mcrypt_generic($this->_config['td'], $source))
        );

        $this->close();
        return $result;
    }

    public function decrypt($source) {
        $source[0] = base64_decode($source[0]);
        $source[1] = base64_decode($source[1]);

        $this->_config['td'] = mcrypt_module_open($this->_config['method'], '', 'ofb', '');
        $this->_config['iv'] = $source[0];

        $ksize = mcrypt_enc_get_key_size($this->_config['td']);
        $key = substr($this->_config['key'], 0, $ksize);
        mcrypt_generic_init($this->_config['td'], $key, $this->_config['iv']);

        $result = mdecrypt_generic($this->_config['td'], $source[1]);
        $result = unserialize($result);

        $this->close();
        return $result;
    }

    private function close() {
        mcrypt_generic_deinit($this->_config['td']);
        mcrypt_module_close($this->_config['td']);
    }
}