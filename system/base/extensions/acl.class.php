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
    private $rules = array();

    public function __construct() {
        $this->rules = require(Config::get('path/config').'acl.php');
    }

    public function isAllowed($role, $resource, $action = '') {
        if ($action == '') {
            return isset($this->rules[$role]['allow'][$resource]);
        }
        else {
            if (isset($this->rules[$role]['allow'][$resource])) {
                $list = $this->rules[$role]['allow'][$resource];
                if ($list === '*')
                    return true;
                else
                    return in_array($action, $list);
            }
            else {
                return false;
            }
        }
    }

    public function isDenied($role, $resource, $action = '') {
        if ($action == '') {
            return isset($this->rules[$role]['deny'][$resource]);
        }
        else {
            if (isset($this->rules[$role]['deny'][$resource])) {
                $list = $this->rules[$role]['deny'][$resource];
                if ($list === '*')
                    return true;
                else
                    return in_array($action, $list);
            }
            else {
                return false;
            }
        }
    }

    public function checkAccess($role, $resource, $action = '') {
        if ($this->rules[$role]['deny'][$resource] === '*') {
            if ($this->isAllowed($role, $resource, $action)) {
                return true;
            }
            return false;
        }
        elseif ($this->rules[$role]['allow'][$resource] === '*') {
            if ($this->isDenied($role, $resource, $action)) {
                return false;
            }
            return true;
        }
        else {
            if ($this->isDenied($role, $resource, $action)) {
                return false;
            }
            elseif ($this->isAllowed($role, $resource, $action)) {
                return true;
            }
        }
        return false;
    }
}