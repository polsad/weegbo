<?php
/**
 * Helper class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
abstract class Helper {

    /**
     * Return value from Registry by name.
     *
     * @access public
     * @param string $name object's name in Registry
     * @return mixed
     */
    public function __get($var) {
        return Registry::get($var);
    }

    abstract public function execute();
}