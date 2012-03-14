<?php
/**
 * Weegbo TruncateHelper class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.helpers
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
require_once(Config::get('path/base').'helper.class.php');
/**
 * TruncateHelper class
 *
 * Helper for truncate string
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.helpers
 * @since 0.8
 */
class TruncateHelper extends Helper {

    public function execute($args = array()) {
        $text = $args[0];
        $number = $args[1];
        $dotted = isset($args[2]) ? $args[2] : '&hellip;';

        $size = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        if ($size > $number) {
            $text = function_exists('mb_substr') ? mb_substr($text, 0, $number, 'UTF-8') : substr($text, 0, $number);
            $text .= $dotted;
        }
        return $text;
    }
}

?>