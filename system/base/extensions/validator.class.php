<?php
/**
 * Weegbo ValidatorExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 *
 * Extension for validate data
 *
 * @package system.base.extensions
 * @since 0.8
 */
class ValidatorExtension {
    private $_errors = array('_general' => NULL);
    private $_rules_js = array();
    private $_rules_php = array();
    private $_seporator = ' ';
    private $_result = true;

    /**
     * Load rules
     */
    public function __construct() {
        $this->_rules_js = require(Config::get('path/config').'validator.php');
        $this->setRulesForPHP($this->_rules_js);
    }

    /**
     * Value for rule validation
     *
     * @access public
     * @param string $value value
     * @param string $rule rule name
     * @param string $error error message
     * @param string $key error key
     * @return bool
     */
    public function rule($value, $rule, $error = NULL, $key = NULL) {
        $result = true;
        $value = trim($value);
        $res = preg_match($this->_rules_php[$rule], $value);
        if ($res == 0) {
            $this->setError($error, $key);
            $result = false;
        }
        return $result;
    }

    /**
     * Value for minimal validation
     *
     * @access public
     * @param float $value
     * @param float $min minimal value
     * @param string $error error message
     * @param string $key error key
     * @return bool
     */
    public function min($value, $min, $error = NULL, $key = NULL) {
        $result = true;
        $value = trim($value);
        if ($value < $min) {
            $this->setError($error, $key);
            $result = false;
        }
        return $result;
    }

    /**
     * Value for maximal validation
     *
     * @access public
     * @param float $value
     * @param float $max maximal value
     * @param string $error error message
     * @param string $key error key
     * @return bool
     */
    public function max($value, $max, $error = NULL, $key = NULL) {
        $result = true;
        $value = trim($value);
        if ($value > $max) {
            $this->setError($error, $key);
            $result = false;
        }
        return $result;
    }

    /**
     * Value for minimal and maximal validation
     *
     * @access public
     * @param float $value
     * @param float $min minimal value
     * @param float $max maximal value
     * @param string $error error message
     * @param string $key error key
     * @return bool
     */
    public function minmax($value, $min, $max, $error = NULL, $key = NULL) {
        $result = true;
        $value = trim($value);
        if ($value > $max || $value < $min) {
            $this->setError($error, $key);
            $result = false;
        }
        return $result;
    }

    /**
     * Value for minimal length validation
     *
     * @access public
     * @param string $value
     * @param int $min minimal length
     * @param string $error error message
     * @param string $key error key
     * @return bool
     */
    public function minlen($value, $min, $error = NULL, $key = NULL) {
        $result = true;
        $value = trim($value);
        if (strlen($value) < $min) {
            $this->setError($error, $key);
            $result = false;
        }
        return $result;
    }

    /**
     * Value for maximal length validation
     *
     * @access public
     * @param string $value
     * @param int $max maximal length
     * @param string $error error message
     * @param string $key error key
     * @return bool
     */
    public function maxlen($value, $max, $error = NULL, $key = NULL) {
        $result = true;
        $value = trim($value);
        if (strlen($value) > $max) {
            $this->setError($error, $key);
            $result = false;
        }
        return $result;
    }

    /**
     * Value for minimal and maximal length validation
     *
     * @access public
     * @param string $value
     * @param int $min minimal length
     * @param int $max maximal length
     * @param string $error error message
     * @param string $key error key
     * @return bool
     */
    public function minmaxlen($value, $min, $max, $error = NULL, $key = NULL) {
        $result = true;
        $value = trim($value);
        $length = strlen($value);
        if ($length > $max || $length < $min) {
            $this->setError($error, $key);
            $result = false;
        }
        return $result;
    }

    /**
     * Checked validation
     *
     * @access public
     * @param string $value
     * @param string $error error message
     * @param string $key error key
     * @return bool
     */
    public function checked($value, $error = NULL, $key = NULL) {
        $result = true;
        if (empty($value)) {
            $this->setError($error, $key);
            $result = false;
        }
        return $result;
    }

    /**
     * Compare two or more values
     *
     * @access public
     * @param array $values values
     * @param string $error error message
     * @param string $key error key
     * @return bool
     */
    public function compare($values, $error = NULL, $key = NULL) {
        $result = true;
        $size = sizeof($values) - 1;
        for ($i = 0; $i < $size; $i++) {
            $a = trim($values[$i]);
            $b = trim($values[$i + 1]);
            if ($a != $b) {
                $this->setError($error, $key);
                $result = false;
                break;
            }
        }
        return $result;
    }

    /**
     * Validate all values
     *
     * @access public
     * @return boolean if validation process finished without errors return true, else - false
     */
    public function validate() {
        return $this->_result;
    }

    /**
     * Set seporator
     *
     * @access public
     * @param string $separator
     * @return void
     */
    public function setSeporator($separator) {
        $this->_seporator = $separator;
    }

    /**
     * Set error
     *
     * @access public
     * @param string $error error message
     * @return void
     */
    public function setError($error, $key) {
        $this->_result = false;
        if ($key == NULL) {
            $this->_errors['_general'] .= $error;
        }
        elseif (array_key_exists($key, $this->_errors)) {
            $this->_errors[$key] .= $error;
        }
        else {
            $this->_errors[$key] = $error;
        }
    }

    /**
     * Return string with all errors
     *
     * @access public
     * @return string
     */
    public function getErrors($string = false) {
        if ($string == true) {
            $this->_errors = join($this->_seporator, $this->_errors);
        }
        return $this->_errors;
    }

    /**
     * Return error keys
     *
     * @access public
     * @return <type>
     */
    public function getErrorsKeys() {
        $keys = array_keys($this->_errors);
        return $keys;
    }

    /**
     * Prepare rules string for JavaScript
     *
     * @access public
     * @return string
     */
    public function getRulesForJS() {
        return json_encode($this->_rules_js);
    }

    /**
     * Prepare rules string for PHP
     *
     * @access private
     * @param array $rules
     * @return void
     */
    private function setRulesForPHP($rules) {
        foreach ($rules as $key => $val) {
            $this->_rules_php[$key] = '/'.$val.'/im';
        }
    }
}
