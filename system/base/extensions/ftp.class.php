<?php
/**
 * Weegbo FtpExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 *
 * Extension for work with FTP
 *
 * @package system.base.extensions
 * @since 0.8
 */
class FtpExtension {
    /**
     * @var resource FTP connection resourse
     */
    private $_connection = NULL;

    /**
     * Connect to FTP server.
     * $config['host'] - ftp host without 'ftp://' prefix
     * $config['port'] - port, default 21
     * $config['user'] - ftp username
     * $config['pass'] - ftp password
     * $config['pasv'] - passive mode (default true)
     *
     * @access public
     * @param array $config array with connection params
     * @return bool
     */
    public function connect($config) {
        $host = isset($config['host']) ? $config['host'] : '';
        $port = isset($config['port']) ? $config['port'] : 21;
        $user = isset($config['user']) ? $config['user'] : '';
        $pass = isset($config['pass']) ? $config['pass'] : '';
        $pasv = isset($config['pasv']) ? $config['pasv'] : true;

        if ($host != '' && $user != '' && $pass != '') {
            $this->_connection = @ftp_connect($host, $port);
            $auth = @ftp_login($this->_connection, $user, $pass);
            if ($this->_connection && $auth) {
                if (true == $pasv) {
                    @ftp_pasv($this->_connection, true);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Close current FTP connection.
     *
     * @access public
     * @return void
     */
    public function close() {
        @ftp_close($this->_connection);
    }

    /**
     * Upload file on server. Default in binary mode.
     *
     * @access public
     * @param string $local_file  path to local file
     * @param string $server_file path to file on server
     * @return bool
     */
    public function upload($local_file, $server_file) {
        if (!$this->status())
            return false;
        $res = @ftp_put($this->_connection, $server_file, $local_file, FTP_BINARY);
        return $res;
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
    public function download($server_file, $local_file) {
        if (!$this->status())
            return false;
        return @ftp_get($this->_connection, $local_file, $server_file, FTP_BINARY);
    }

    /**
     * Delete file from server.
     *
     * @access public
     * @param string $server_file path to file on server
     * @return bool
     */
    public function delete($server_file) {
        if (!$this->status())
            return false;
        return @ftp_delete($this->_connection, $server_file);
    }

    /**
     * Rename file or directory on server.
     *
     * @access public
     * @param string $old_name old name
     * @param string $new_name new name
     * @return bool
     */
    public function rename($old_name, $new_name) {
        if (!$this->status())
            return false;
        return @ftp_rename($this->_connection, $old_name, $new_name);
    }

    /**
     * Change directory.
     *
     * @access public
     * @param string $dir_name directory name
     * @return string|NULL
     */
    public function cd($dir_name) {
        if (!$this->status())
            return false;
        return @ftp_chdir($this->_connection, $dir_name);
    }

    /**
     * Current directory.
     *
     * @access public
     * @return string|NULL
     */
    public function currdir() {
        if (!$this->status())
            return false;
        $dir = @ftp_pwd($this->_connection);
        if (false == $dir)
            $dir == NULL;
        return $dir;
    }

    /**
     * Set access mode.
     *
     * @access public
     * @param int $mode unix mode (for example - 0644)
     * @param string $server_file path to file on server
     * @return string|NULL
     */
    public function chmod($server_file, $mode) {
        if (!$this->status())
            return false;
        if (@ftp_chmod($this->_connection, $mode, $server_file) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Create directory.
     *
     * @access public
     * @param string $dir_name directory name
     * @param int $mode unix mode (for example - 0644)
     * @return bool
     */
    public function mkdir($dir_name, $mode = '') {
        if (!$this->status())
            return false;
        if (!@ftp_mkdir($this->_connection, $dir_name)) {
            return false;
        }
        if ($mode != '') {
            $this->chmod($dir_name, $mode);
        }
        return true;
    }

    /**
     * Delete directory from server.
     *
     * @access public
     * @param string $dir_name directory name
     * @return bool
     */
    public function rmdir($dir_name) {
        if (!$this->status())
            return false;
        if (substr($dir_name, -1) != '/')
            $dir_name .= '/';
        $filelist = $this->ls($dir_name);
        if ($filelist != NULL) {
            foreach ($filelist as $item) {
                if ($item != $dir_name.'.' && $item != $dir_name.'..') {
                    if (!$this->delete($item)) {
                        $this->rmdir($item);
                    }
                }
            }
        }
        return @ftp_rmdir($this->_connection, $dir_name);
    }

    /**
     * Return list files and directoris $dir_name.
     *
     * @access public
     * @param string $dir_name directory name
     * @return string|NULL
     */
    public function ls($dir_name = '.') {
        if (!$this->status())
            return false;
        $res = @ftp_nlist($this->_connection, $dir_name);
        if ($res == false) {
            $res = NULL;
        }
        return $res;
    }

    /**
     * Return status ftp connection.
     *
     * @access private
     * @return bool
     */
    private function status() {
        if (is_resource($this->_connection)) {
            return true;
        }
        return false;
    }
}
