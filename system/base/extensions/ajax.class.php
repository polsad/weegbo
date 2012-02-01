<?php
/**
 * Weegbo AjaxExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions
 * @since 0.8
 */
class AjaxExtension {

    /**
     * Return true if it is a AJAX query, or false is not
     *
     * @access public
     * @return bool
     */
    public function isAjax() {
        $result = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
        return $result;
    }

    /**
     * Print string with data in JSON format
     *
     * @access public
     * @param mixed $data hash for convert to JSON format and print it
     * @return void
     */
    public function sendData($data) {
        $date = json_encode($data);
        echo $date;
        exit();
    }
}
