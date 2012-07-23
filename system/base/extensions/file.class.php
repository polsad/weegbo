<?php
/**
 * Weegbo FileExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 *
 * Extension for work with file system
 *
 * @package system.base.extensions
 * @since 0.8
 */
class FileExtension {
    private $_mimes = null;

    /**
     * Save upload file.
     *
     * @access public
     * @param string $dir       directory for file save
     * @param string $file_name filename
     * @param string $file_tmp  source filename (from $_FILES array)
     * @return bool
     */
    public function saveUploadFile($dir, $file_name, $file_tmp) {
        if (!is_dir($dir)) {
            $this->createDir($dir);
        }
        @move_uploaded_file($file_tmp, $dir.$file_name);
        return true;
    }

    public function checkUploadFile($file, $index = null) {
        $result = true;
        if (null != $file) {
            /**
             * Check all files array
             */
            if (is_array($file['error']) && $index === null) {
                $size = sizeof($file['error']);
                for ($i = 0; $i < $size; $i++) {
                    if ($file['error'][$i] > 0 || $file['size'][$i] == 0) {
                        $result = false;
                    }
                }
            }
            /**
             * Check index file from array
             */
            else if (is_array($file['error']) && $index !== null) {
                if (isset($file['error'][$index])) {
                    $result = ($file['error'][$index] == 0 && $file['size'][$index] > 0) ? true : false;
                }
                else {
                    $result = false;
                }
            }
            /**
             * Check single file
             */
            else {
                $result = ($file['error'] == 0 && $file['size'] > 0) ? true : false;
            }
        }
        return $result;
    }

    /**
     * Remove file.
     * For example: removeFile('/usr/host/123/images/1.jpg', '/usr/host/123/images/new/1.jpg');
     *
     * @access public
     * @param string $source      path to source file
     * @param string $destination path to destination file
     * @return bool
     */
    public function removeFile($source, $destination) {
        if (!file_exists($source)) {
            return false;
        }
        $result = @copy($source, $destination);
        if ($result != false) {
            $this->deleteFile($source);
        }
        return $result;
    }

    /**
     * Delete файла.
     * For example: deleteFile('/usr/host/123/images/1.jpg');
     *
     * @access public
     * @param string $source path to file
     * @return bool
     */
    public function deleteFile($source) {
        $result = false;
        if (is_file($source)) {
            @unlink($source);
            $result = true;
        }
        return $result;
    }

    /**
     * Create directory.
     *
     * @access public
     * @param string $dir  полный путь к каталогу
     * @param octal $chmod права доступа к каталогу
     * @return bool
     */
    public function createDir($dir, $chmod = 0755) {
        $result = true;
        if (!is_dir($dir)) {
            $result = @mkdir($dir, $chmod);
        }
        return $result;
    }

    /**
     * Return list of files from dir
     *
     * @access public
     * @param string $dir 
     * @return array
     */
    public function getFilesFromDir($dir) {
        $files = array();
        $dir = rtrim($dir, '/').'/';
        if (is_dir($dir)) {
            $dh = opendir($dir);
            if ($dh) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        $size = filesize($dir.'/'.$file);
                        $files[] = array('file' => $file, 'size' => sprintf("%01.2f", $size / 1024));
                    }
                }
                closedir($dh);
            }
        }
        /**
         * Sort filename based on local
         */                 
        sort($files, SORT_LOCALE_STRING);
        return $files;
    }

    /**
     * Delete directory with all subdirectories and files.
     * Source method - http://www.php.net/manual/ru/function.rmdir.php#87385.
     *
     * @access public
     * @param string $dir полный путь к каталогу
     * @return bool
     */
    public function deleteDir($dir) {
        if (!file_exists($dir)) {
            return false;
        }
        $dir = rtrim($dir, '/').'/';
        $handle = opendir($dir);
        for (; false !== ($file = readdir($handle));) {
            if ($file != "." and $file != "..") {
                $path = $dir.$file;
                if (is_dir($path)) {
                    $this->deleteDir($path);
                    $res = @rmdir($path);
                }
                else
                    @unlink($path);
            }
        }
        closedir($handle);
        $res = @rmdir($dir);
        return $res;
    }

    /**
     * Return array with file name and file extension.
     * For example: getFileNameExt('avatar.jpg') return array ('name' => 'avatar', 'ext' => 'jpg');
     *
     * @access public
     * @param string имя файла
     * @return array or null
     */
    public function getFileNameExt($file) {
        $pos = strrpos($file, '.');
        $result = array(
            'name' => substr($file, 0, $pos), 
            'ext' => substr($file, $pos + 1)
        );
        return $result;
    }

    /**
     * Check allowed file type.
     * For example: checkFileType('image/jpeg', array('jpg','gif','png') return true,
     *              checkFileType('text/plain', array('jpg','gif','png') return false
     *
     * @access public
     * @param string $file_type mime file type
     * @param array $allow_types array with allowed file types
     * @example checkFileType('image/jpeg', array('jpg','gif','png') return true,
     *              checkFileType('text/plain', array('jpg','gif','png') return false
     * @return bool
     */
    public function checkFileType($file_type, $allow_types) {
        if (null === $this->_mimes) {
            $this->_mimes = require(Config::get('path/config').'mimes.php');
        }
        $flag = false;
        $needle = array();

        foreach ($this->_mimes as $key => $value) {
            if ($file_type === $value) {
                $needle[] = $key;
            }
            if (is_array($value)) {
                for ($i = 0; $i < sizeof($value); $i++) {
                    if ($file_type === $value[$i]) {
                        $needle[] = $key;
                    }
                }
            }
        }
        if (null != $needle) {
            for ($i = 0; $i < sizeof($needle); $i++) {
                if (in_array($needle[$i], $allow_types)) {
                    $flag = true;
                    break;
                }
            }
        }
        return $flag;
    }
}