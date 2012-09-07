<?php
/**
 * Weegbo SessionExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 *
 * Extension for work with session
 *
 * @package system.base.extensions
 * @since 0.8
 */
class SessionExtension {
    private $_name = 'session';

    /**
     * Init session
     *
     * @access public
     * @param string $subdomains cookie would be access in all subdomains this domain
     * @return void
     */
    public function __construct($subdomains = null) {
        /**
         * Init session and create 'session' array
         */
        $this->_name = Config::get('app/session-name');
        if (!isset($_SESSION)) {
            if (null != $subdomains) {
                session_set_cookie_params('Session', '/', '.'.$subdomains);
            }
            session_start();
        }
        if (!isset($_SESSION[$this->_name])) {
            $_SESSION[$this->_name] = array();
        }
    }

    /**
     * Set new variable to session.
     *
     * @access public
     * @param string $name  variable name
     * @param mixed  $value variable value
     * @return void
     */
    public function set($name, $value) {
        $_SESSION[$this->_name][$name] = $value;
    }

    /**
     * Get variable value, if mode = 'del' delete this variable from session.
     *
     * @access public
     * @param string $name variable name
     * @return mixed
     */
    public function get($name) {
        $value = isset($_SESSION[$this->_name][$name]) ? $_SESSION[$this->_name][$name] : null;
        return $value;
    }

    /**
     * Delete variable from session.
     *
     * @access public
     * @param string $name variable name
     * @return void
     */
    public function delete($name) {
        unset($_SESSION[$this->_name][$name]);
    }

    /**
     * Set or get session ID
     * 
     * @access public
     * @param type $sid
     * @return string 
     */
    public function sessionId($sid = '') {
        if ($sid == '') {
            $sid = session_id();
        }
        else {
            session_id($sid);
        }
        return $sid;
    }

    /**
     * Check variable name in session.
     *
     * @access public
     * @return bool
     */
    public function check($name) {
        $result = (isset($_SESSION[$this->_name][$name])) ? true : false;
        return $result;
    }
}