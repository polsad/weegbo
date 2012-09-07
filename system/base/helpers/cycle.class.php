<?php
/**
 * Weegbo CycleHelper class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.helpers
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
require_once(Config::get('path/base').'helper.class.php');
/**
 * CycleHelper class
 *
 * Helper for output element in cycle
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.helpers
 * @since 0.8
 */
class CycleHelper extends Helper {
    /**
     * @var array helper arguments
     */
    private $_args = array();

    public function execute($args = array()) {
        $name = isset($args[0]) ? $args[0] : null;
        if (!isset($this->_args[$name])) {
            $this->_args[$name]['vals'] = array_slice($args, 1);
            $this->_args[$name]['index'] = 0;
            $this->_args[$name]['limit'] = sizeof($this->_args[$name]['vals']) - 1;
        }
        else {
            if ($this->_args[$name]['index'] < $this->_args[$name]['limit']) {
                $this->_args[$name]['index']++;
            }
            else {
                $this->_args[$name]['index'] = 0;
            }
        }
        return $this->_args[$name]['vals'][$this->_args[$name]['index']];
    }
}

?>