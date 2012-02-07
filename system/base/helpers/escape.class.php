<?php
/**
 * Weegbo EscapeHelper class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.helpers
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
require_once(PATH_BASE.'helper.class.php');
/**
 * EscapeHelper class
 *
 * Helper for escape output data
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.helpers
 * @since 0.8
 */
class EscapeHelper extends Helper {

    public function execute($args = array()) {
        $value = isset($args[0]) ? $args[0] : '';
        if (!empty($value)) {
            if (get_magic_quotes_gpc() == true) {
                $value = stripcslashes($value);
            }
            $value = htmlspecialchars($value);
        }
        return $value;
    }
}

?>