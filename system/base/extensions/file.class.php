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
     * Check uploaded file
     * Return error upload code
     *
     * UPLOAD_ERR_OK => "No errors.",
     * UPLOAD_ERR_INI_SIZE => "Larger than upload_max_filesize.",
     * UPLOAD_ERR_FORM_SIZE => "Larger than form MAX_FILE_SIZE.",
     * UPLOAD_ERR_PARTIAL => "Partial upload.",
     * UPLOAD_ERR_NO_FILE => "No file.",
     * UPLOAD_ERR_NO_TMP_DIR => "No temporary directory.",
     * UPLOAD_ERR_CANT_WRITE => "Can't write to disk.",
     * UPLOAD_ERR_EXTENSION => "File upload stopped by extension.", 
     * UPLOAD_ERR_EMPTY => "File is empty"
     *
     * @access public
     * @param string имя файла
     * @return int
     */     
     
    public function uploadCheckFile($file, $index = null) {
        if (defined('UPLOAD_ERR_EMPTY')) {
            define('UPLOAD_ERR_EMPTY', 5);
        }
        $result = UPLOAD_ERR_NO_FILE;
        if (null != $file) {
            /**
             * Check all files array
             */
            if (is_array($file['error']) !== true && $index !== null) {
                if (isset($file['error'][$index])) {
                    $result = ($file['size'][$index] == 0 && $file['error'][$index] == 0) ? UPLOAD_ERR_EMPTY : $file['error'][$index];
                }
                else {
                    $result = UPLOAD_ERR_NO_FILE;
                }
            }
            if (is_array($file['error']) === false) {
                $result = $file['error'];
            }
        }
        return $result;
    }

    public function uploadSaveFile($source, $dest) {
        $pos = strrpos($dest, '/');
        $dir = substr($dest, 0, $pos + 1);
        if (false == is_dir($dir)) {
            $this->createDir($dir);
        }
        $result = @move_uploaded_file($source, $dest);
        return $result;
    }

    /**
     * Return array with file name and file extension.
     * For example: getFileNameExt('avatar.jpg') return array ('avatar', 'jpg');
     *
     * @access public
     * @param string имя файла
     * @return array or null
     */
    public function getFileName($file) {
        $pos = strrpos($file, '.');
        $result = array(
            'dir' => dirname($file),
            'name' => substr($file, 0, $pos),
            'ext' => substr($file, $pos + 1)
        );
        if ($result['dir'] != '.' && $result['dir'] != '..') {
            $result['name'] = str_replace($result['dir'], '', $result['name']);
            $result['name'] = ltrim($result['name'], '/');
        }
        else {
            $result['dir'] = null;
        }
        return $result;
    }

    /**
     * Check allowed file type.
     * For example: checkFileType('image/jpeg', array('jpg','gif','png') return true,
     *              checkFileType('text/plain', array('jpg','gif','png') return false
     *
     * @access public
     * @param string $fileType mime file type
     * @param array $allowTypes array with allowed file types
     * @example checkFileType('image/jpeg', array('jpg','gif','png') return true,
     *              checkFileType('text/plain', array('jpg','gif','png') return false
     * @return bool
     */
    public function checkFileType($fileType, $allowTypes) {
        if (null === $this->_mimes) {
            $this->_mimes = require(Config::get('path/config').'mimes.php');
        }
        $flag = false;
        $needle = array();

        foreach ($this->_mimes as $key => $value) {
            if ($fileType === $value) {
                $needle[] = $key;
            }
            if (is_array($value)) {
                for ($i = 0; $i < sizeof($value); $i++) {
                    if ($fileType === $value[$i]) {
                        $needle[] = $key;
                    }
                }
            }
        }
        if (null != $needle) {
            for ($i = 0; $i < sizeof($needle); $i++) {
                if (in_array($needle[$i], $allowTypes)) {
                    $flag = true;
                    break;
                }
            }
        }
        return $flag;
    }

    /**
     * Remove file.
     * For example: removeFile('/usr/host/123/images/1.jpg', '/usr/host/123/images/new/1.jpg');
     *
     * @access public
     * @param string $source path to source file
     * @param string $dest path to destination file
     * @return bool
     */
    public function mv($source, $dest) {
        $result = false;
        if (true === file_exists($source)) {
            $result = @copy($source, $dest);
            if ($result === true) {
                $this->rm($source);
            }
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
    public function rm($source) {
        $result = false;
        if (true === is_file($source)) {
            $result = @unlink($source);
        }
        return $result;
    }

    /**
     * Create directory.
     *
     * @access public
     * @param string $dir 
     * @param octal $chmod
     * @return bool
     */
    public function mkdir($dir, $chmod = 0755) {
        $result = true;
        $dir = rtrim($dir, '/').'/';
        if (false === is_dir($dir)) {
            $result = @mkdir($dir, $chmod);
        }
        return $result;
    }

    /**
     * Delete directory with all subdirectories and files.
     * Source method - http://www.php.net/manual/ru/function.rmdir.php#108113
     *
     * @access public
     * @param string $dir 
     * @return bool
     */
    public function rmdir($dir) {
        $result = false;
        $dir = rtrim($dir, '/').'/';
        if (true === file_exists($dir)) {
            foreach (glob($dir.'*') as $file) {
                if (is_dir($file)) {
                    $this->deleteDir($file);
                }
                else {
                    $this->rm($file);
                }
            }
            $result = @rmdir($dir);
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
    public function ls($dir, $type = '*') {
        $files = array();
        $dir = rtrim($dir, '/').'/';
        if (true === is_dir($dir)) {
            $buff = glob($dir.'*');
            sort($buff, SORT_LOCALE_STRING);
            foreach ($buff as $file) {
                if (true === is_file($file) && ($type === '*' || $type == 'files')) {
                    $files[] = array(str_replace($dir, '', $file), 'file', filesize($file));
                }
                if (true === is_dir($file) && ($type === '*' || $type == 'dirs')) {
                    $files[] = array(str_replace($dir, '', $file), 'dir');
                }
            }
        }
        return $files;
    }
}