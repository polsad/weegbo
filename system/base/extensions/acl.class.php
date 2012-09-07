<?php
/**
 * Weegbo AclExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base.extensions
 * @since 0.8
 */
class AclExtension {
    /**
     * @var array roles
     */
    private $_rules = array();

    public function __construct($config = null) {
        if ($config != null) {
            $this->setConfig($config);
        }
    }

    public function setConfig($config) {
        if ($config != null) {
            $isArray = is_array($config);
            if ($isArray === false && file_exists(Config::get('path/config').$config)) {
                $this->_rules = require(Config::get('path/config').$config);
            }
            else {
                $this->_rules = $config;
            }
        }
    }

    public function isAllowed($role, $resource) {
        return $this->_checkRole('allow', $role, $resource);
    }

    public function isDenied($role, $resource) {
        return $this->_checkRole('deny', $role, $resource);
    }

    public function checkAccess($role, $resource) {
        $result = false;
        if (isset($this->_rules[$role]['deny']) && !isset($this->_rules[$role]['allow'])) {
            $result = $this->isDenied($role, $resource);
        }
        elseif (isset($this->_rules[$role]['allow']) && !isset($this->_rules[$role]['deny'])) {
            $result = $this->isAllowed($role, $resource);
        }
        elseif ($this->_rules[$role]['deny'] === '*') {
            $result = $this->isAllowed($role, $resource);
        }
        elseif ($this->_rules[$role]['allow'] === '*') {
            $result = $this->isDenied($role, $resource);
        }
        else {
            if ($this->isDenied($role, $resource)) {
                $result = false;
            }
            elseif ($this->isAllowed($role, $resource)) {
                $result = true;
            }
        }
        return $result;
    }

    private function _checkRole($type, $role, $resource) {
        $result = false;
        if (isset($this->_rules[$role][$type])) {
            if (is_array($this->_rules[$role][$type])) {
                if (array_search($resource, $this->_rules[$role][$type]) !== false) {
                    $result = true;
                }
            }
            else {
                if ($this->_rules[$role][$type] === '*' || $this->_rules[$role][$type] === $resource) {
                    $result = true;
                }
            }
        }
        return $result;
    }
}