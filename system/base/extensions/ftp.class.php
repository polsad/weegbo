<?php
/**
 * Weegbo FtpExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 *
 * Extension for work with FTP
 *
 * @package system.base.extensions
 * @since 0.8
 */
class FtpExtension {
    /**
     * @var bool $_flag set config flag
     */
    private $_flag = false;

    /**
     * @var array $_config FTP config
     */
    private $_config = array(
        'host' => '',
        'port' => 21,
        'user' => 'anonymous',
        'password' => 'anonymous@example.com',
        'ssl' => false,
        'passive-mode' => true,
        'timeout' => 90,
        'auto-connect' => true
    );

    /**
     * @var resource $_connect FTP connection resourse
     */
    private $_connect = null;

    public function __construct($config = null) {
        $this->setConfig($config);
        if ($this->_config['auto-connect'] === true) {
            $this->init();
        }
    }

    public function __destruct() {
        $this->close();
    }

    public function setConfig($config) {
        $this->_flag = false;
        if ($config !== null && is_array($config) == true) {
            foreach ($this->_config as $k => $v) {
                $this->_config[$k] = (array_key_exists($k, $config) === false) ? $v : $config[$k];
            }
            $this->_flag = true;
        }
        return $this->_flag;
    }

    /**
     * Connect to FTP server.
     *
     * @access public
     * @param array $config array with connection params
     * @return bool
     */
    public function init() {
        $result = false;
        if ($this->_flag === true) {
            // Connect to FTP
            if ($this->_config['ssl'] === true) {
                $this->_connect = @ftp_ssl_connect($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
            }
            else {
                $this->_connect = @ftp_connect($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
            }
            // FTP login
            $authFlag = @ftp_login($this->_connect, $this->_config['user'], $this->_config['password']);
            if ($this->_connect && $authFlag == true) {
                if (true == $this->_config['passive-mode']) {
                    @ftp_pasv($this->_connect, true);
                }
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Close current FTP connection.
     *
     * @access public
     * @return void
     */
    public function close() {
        $result = true;
        if ($this->_checkConnect()) {
            $result = @ftp_close($this->_connect);
        }
        return $result;
    }

    /**
     * Upload file on server. Default in binary mode.
     *
     * @access public
     * @param string $localFile  path to local file
     * @param string $serverFile path to file on server
     * @return bool
     */
    public function put($localFile, $serverFile) {
        $result = false;
        if ($this->_checkConnect()) {
            $result = @ftp_put($this->_connect, $serverFile, $localFile, FTP_BINARY);
        }
        return $result;
    }

    /**
     * Download file from server. Default in binary mode. If local file is exist,
     * it would be rewrite.
     *
     * @access public
     * @param string $server_file path to file on server
     * @param string $local_file  path to local file
     * @return bool
     */
    public function get($serverFile, $localFile) {
        $result = false;
        if ($this->_checkConnect()) {
            $result = @ftp_get($this->_connect, $localFile, $serverFile, FTP_BINARY);
        }
        return $result;
    }

    /**
     * Delete file from server.
     *
     * @access public
     * @param string $$serverFile path to file on server
     * @return bool
     */
    public function rm($serverFile) {
        $result = false;
        if ($this->_checkConnect()) {
            $result = @ftp_delete($this->_connect, $serverFile);
        }
        return $result;
    }

    /**
     * Rename file or directory on server.
     *
     * @access public
     * @param string $oldServerName old name
     * @param string $newServerName new name
     * @return bool
     */
    public function mv($oldServerName, $newServerName) {
        $result = false;
        if ($this->_checkConnect()) {
            $result = @ftp_rename($this->_connect, $oldServerName, $newServerName);
        }
        return $result;
    }

    /**
     * Set access mode.
     *
     * @access public
     * @param int $mode unix mode (for example - 0644)
     * @param string $serverFile path to file on server
     * @return string|null
     */
    public function chmod($serverFile, $mode) {
        $result = false;
        if ($this->_checkConnect()) {
            $result = @ftp_chmod($this->_connect, $mode, $serverFile);
            $result = ($result === false) ? false : true;
        }
        return $result;
    }

    /**
     * Change directory.
     *
     * @access public
     * @param string $directory directory name
     * @return string|null
     */
    public function chdir($directory) {
        $result = null;
        if ($this->_checkConnect()) {
            $result = @ftp_chdir($this->_connect, $directory);
        }
        return $result;
    }

    /**
     * Current directory.
     *
     * @access public
     * @return string|null
     */
    public function dirname() {
        $result = null;
        if ($this->_checkConnect()) {
            $result = @ftp_pwd($this->_connect);
            $result = ($result == false) ? null : $result;
        }
        return $result;
    }

    /**
     * Create directory.
     *
     * @access public
     * @param string $dir directory name
     * @param int $mode unix mode (for example - 0644)
     * @return bool
     */
    public function mkdir($dir, $mode = '') {
        $result = false;
        if ($this->_checkConnect()) {
            $result = @ftp_mkdir($this->_connect, $dir);
            $result = ($result == false) ? null : $result;
        }
        if ($mode != '') {
            $result = $this->chmod($dir, $mode);
        }
        return $result;
    }

    /**
     * Delete directory from server.
     *
     * @access public
     * @param string $dir directory name
     * @return bool
     */
    public function rmdir($dir) {
        $result = true;
        if ($this->_checkConnect()) {
            $dir = rtrim($dir, '/').'/';
            $buff = ftp_rawlist($this->_connect, $dir, true);

            $dirs = array($dir);
            $files = array();
            $tmpdir = $dir;

            foreach ((array)$buff as $rawfile) {
                $info = preg_split("/[\s]+/", $rawfile, 9);
                $size = sizeof($info);
                if ($size == 1 && $info[0] != '') {
                    $tmpdir = $info[0];
                    $tmpdir = rtrim($tmpdir, ':');
                    $tmpdir = str_replace('//', '/', $tmpdir);
                    $tmpdir .= '/';
                    $dirs[] = $tmpdir;
                }
                if ($size == 9 && $info[0]{0} != 'd') {
                    $files[] = $tmpdir.$info[8];
                }
            }
            foreach ((array)$files as $v) {
                $buff = $this->rm($v);
                if ($buff == false) {
                    $result = false;
                }
            }
            $dirs = array_reverse($dirs);
            foreach ($dirs as $v) {
                $buff = @ftp_rmdir($this->_connect, $v);
                if ($buff == false) {
                    $result = false;
                }
            }
        }
        return $result;
    }

    /**
     * Return list files and directoris $dir_name.
     *
     * @access public
     * @param string $dir directory name
     * @return string|null
     */
    public function ls($dir = '.') {
        $result = false;
        if ($this->_checkConnect()) {
            $result = @ftp_nlist($this->_connect, $dir);
            $result = ($result === false) ? null : $result;
        }
        return $result;
    }

    /**
     * Return status ftp connection.
     *
     * @access private
     * @return bool
     */
    private function _checkConnect() {
        return is_resource($this->_connect);
    }
}