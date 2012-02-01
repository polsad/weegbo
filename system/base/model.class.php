<?php
/**
 * Model class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class Model {

    /**
     * Return value from Registry by name
     *
     * @access public
     * @param string $name object's name in Registry
     * @return mixed
     */
    public function __get($var) {
        return Registry::get($var);
    }
}
