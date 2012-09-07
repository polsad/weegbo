<?php
/**
 * Weegbo DomExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.extensions
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
/**
 * DomExtension class
 *
 * Class for make XML.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.extensions
 * @since 0.8
 */
class DbExtension {
    /**
     * @var array $_pdo PDO objects
     */
    private $_pdo = null;

    /**
     * @var array $_sth PDOStatement objects
     */
    private $_sth = null;

    /**
     * @var array $_prefix table prefixes
     */
    private $_prefix = null;

    /**
     * @var array config 
     */
    private $_config = array();

    /**
     * @var int $_time 
     */
    private $_time;

    /**
     * @var string $_query query
     */
    private $_query = null;

    /**
     * @var array $_query_args query arguments
     */
    private $_query_args = array();

    /**
     * @var array $_query_params query parametrs
     */
    private $_query_params = array();

    /**
     * @var bool $_query_transform
     */
    private $_query_transform = false;

    /**
     * Connect to database
     *
     * @access public
     * @param string $config connection name in daatabase config
     * @return void
     */
    public function __construct($config = null) {
        if (!extension_loaded('PDO')) {
            throw new CException('Db requires PHP PDO extension to be loaded', 500);
        }
        $this->init($config);
    }

    public function init($config = null) {
        if ($config !== null) {
            $this->_setConfig($config);
        }
        try {
            $this->_pdo = new PDO($this->_config['driver'].':host='.$this->_config['host'].';dbname='.$this->_config['db'].';charset='.$this->_config['charset'], $this->_config['user'], $this->_config['pass']);
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_prefix = $this->_config['prefix'];
            if (Config::get('profiler/level')) {
                Profiler::add("Db connected to {$this->_config['db']}", 'db-connect');
            }
        }
        catch (PDOException $e) {
            throw new CException($e->getMessage(), 500);
        }
    }

    /**
     * Execute select query and return the result.
     *
     * @access public
     * @return mixed array or null
     */
    public function select() {
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $result = $this->_executeQuery($args);
            $result = $this->_sth->fetchAll(PDO::FETCH_ASSOC);
            if (preg_match('/ARRAY_KEY/six', $this->_query)) {
                $result = $this->_transformResult($result, 'ARRAY_KEY');
            }
            $this->_cleanQueryParams();
            return $result;
        }
        else {
            return null;
        }
    }

    /**
     * Select one row and return result
     *
     * @access public
     * @return mixed array or null
     */
    public function selectRow() {
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $result = $this->_executeQuery($args);
            $result = $this->_sth->fetch(PDO::FETCH_ASSOC);
            // http://www.php.net/manual/en/pdostatement.fetch.php#74262
            $this->_sth->closeCursor();
            if (preg_match('/ARRAY_KEY/six', $this->_query)) {
                $result = $this->_transformResult($result, 'ARRAY_KEY');
            }
            $this->_cleanQueryParams();
            return $result;
        }
        else {
            return null;
        }
    }

    /**
     * Select one column and return result
     *
     * @access public
     * @return mixed array or null
     */
    public function selectCol() {
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $result = $this->_executeQuery($args);
            $result = $this->_sth->fetchAll();
            $buff = array();
            $size = sizeof($result);
            if (preg_match('/ARRAY_KEY/six', $this->_query)) {
                for ($i = 0; $i < $size; $i++) {
                    $buff[$result[$i]['ARRAY_KEY']] = $result[$i][1];
                }
            }
            else {
                for ($i = 0; $i < $size; $i++) {
                    $buff[] = $result[$i][0];
                }
            }
            $this->_cleanQueryParams();
            return $buff;
        }
        else {
            return null;
        }
    }

    /**
     * Select scalar value and return result
     *
     * @access public
     * @return mixed or null
     */
    public function selectCell() {
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $result = $this->_executeQuery($args);
            $result = $this->_sth->fetch(PDO::FETCH_NUM);
            // http://www.php.net/manual/en/pdostatement.fetch.php#74262
            $this->_sth->closeCursor();
            $this->_cleanQueryParams();
            return $result[0];
        }
        else {
            return null;
        }
    }

    /**
     * Execute query and return the result.
     *
     * @access public
     * @return mixed int or null
     */
    public function query() {
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $result = $this->_executeQuery($args);
            if (preg_match('/^\s* INSERT \s+/six', $this->_query)) {
                $result = $this->_pdo->lastInsertId();
            }
            if (preg_match('/ARRAY_KEY/six', $this->_query)) {
                $result = $this->_transformResult($result, 'ARRAY_KEY');
            }
            $this->_cleanQueryParams();
            return $result;
        }
        else {
            return null;
        }
    }

    /**
     * Returns the number of rows affected by the last DELETE, INSERT, or UPDATE
     * http://www.php.net/manual/en/pdostatement.rowcount.php
     *
     * @access public
     * @return int
     */
    public function rowCount() {
        $result = (int) $this->_sth->rowCount();
        return $result;
    }

    /**
     * Start transaction
     *
     * @access public
     * @return bool
     */
    public function transaction() {
        return $this->_pdo->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * @access public
     * @return bool
     */
    public function commit() {
        return $this->_pdo->commit();
    }

    /**
     * Rollback transaction
     *
     * @access public
     * @return bool
     */
    public function rollback() {
        return $this->_pdo->rollBack();
    }

    private function _setConfig($config) {
        $isArray = is_array($config);
        // If not loaded, load database config
        if ($isArray === false && Config::get('database', true) == null) {
            Config::load(Config::get('config/database'));
        }
        if ($isArray === false && Config::get("database/{$config}", true) == null) {
            throw new CException("Can't find '{$config}' connection in database config", 500);
        }
        $keys = array('driver', 'host', 'user', 'pass', 'db', 'prefix', 'charset');
        for ($i = 0; $i < 7; $i++) {
            if ($isArray === true) {
                if (false === array_key_exists($keys[$i], $config)) {
                    throw new CException("{$keys[$i]} not found in database config", 500);
                }
                else {
                    $this->_config[$keys[$i]] = $config[$keys[$i]];
                }
            }
            else {
                $this->_config[$keys[$i]] = Config::get("database/{$config}/{$keys[$i]}");
            }
        }
    }

    /**
     * Prepare query for PDO execute.
     *
     * @param array $args arguments passed to the queries methods
     * @access private
     * @return void
     */
    private function _prepareQuery($args) {
        $this->_query = $args[0];
        $this->_query = str_replace('?_', $this->_prefix, $this->_query);
        $this->_query_args = array_slice($args, 1);

        $regexp = '{(\?)( [dsafn\#] ?)}sx';
        $this->_query = preg_replace_callback($regexp, array('self', 'expandPlaceholders'), $this->_query);
    }

    /**
     * Execute query and return result.
     *
     * @access private
     * @param array $args arguments passed to the queries methods
     * @return mixed
     */
    private function _executeQuery($args) {
        $this->_queryRuntime('start');
        $this->_prepareQuery($args);
        $this->_sth = $this->_pdo->prepare($this->_query);
        $size = sizeof($this->_query_params);
        $index = 1;
        for ($i = 0; $i < $size; $i++) {
            switch ($this->_query_params[$i][1]) {
                case 'int':
                    $this->_query_params[$i][0] = (int) $this->_query_params[$i][0];
                    $this->_sth->bindValue($index, $this->_query_params[$i][0], PDO::PARAM_INT);
                    break;
                case 'float':
                    $this->_query_params[$i][0] = (float) $this->_query_params[$i][0];
                    $this->_sth->bindValue($index, $this->_query_params[$i][0]);
                    break;
                case 'string':
                    $this->_query_params[$i][0] = (string) $this->_query_params[$i][0];
                    if (get_magic_quotes_gpc()) {
                        $this->_query_params[$i][0] = stripcslashes($this->_query_params[$i][0]);
                    }
                    $this->_sth->bindValue($index, $this->_query_params[$i][0], PDO::PARAM_STR);
                    break;
                case 'default':
                    $this->_query_params[$i][0] = $this->_query_params[$i][0];
                    if (get_magic_quotes_gpc()) {
                        $this->_query_params[$i][0] = stripcslashes($this->_query_params[$i][0]);
                    }
                    $this->_sth->bindValue($index, $this->_query_params[$i][0]);
                    break;
            }
            $index++;
        }
        try {
            $result = $this->_sth->execute();
        }
        catch (PDOException $e) {
            throw new CException($e->getMessage(), 500);
        }
        return $result;
    }

    /**
     * Expand placeholders.
     *
     * @access private
     * @param array $match
     * @return string
     */
    private function _expandPlaceholders($match) {
        $result = $match[0];
        if (!empty($match[0])) {
            $type = $match[2];
            if (!$this->_query_args)
                return 'DB_ERROR_NO_VALUE';
            $param = array_shift($this->_query_args);
            switch ($type) {
                case 'd':
                    $this->_query_params[] = array($param, 'int');
                    $result = '?';
                    break;
                case 'f':
                    $this->_query_params[] = array($param, 'float');
                    $result = '?';
                    break;
                case 'a':
                    if (!is_array($param)) {
                        $result = 'DB_ERROR_VALUE_NOT_ARRAY';
                    }
                    else {
                        $parts = array();
                        foreach ($param as $k => $v) {
                            if (!is_int($k)) {
                                $this->_query_params[] = array($v, 'default');
                                $k = $this->_escape($k, true);
                                $parts[] = "$k = ?";
                            }
                            else {
                                $this->_query_params[] = array($v, 'default');
                                $parts[] = '?';
                            }
                        }
                        $result = join(', ', $parts);
                    }
                    break;
                case 'n':
                    $param = (int) $param;
                    if ($param == 0) {
                        $result = 'null';
                    }
                    else {
                        $this->_query_params[] = array($param, 'int');
                        $result = '?';
                    }
                    break;
                case 's':
                    $this->_query_params[] = array($param, 'string');
                    $result = '?';
                    break;
                case '#':
                    if (!is_array($param)) {
                        $result = 'DB_ERROR_VALUE_NOT_ARRAY';
                    }
                    else {
                        $parts = array();
                        $size = sizeof($param);
                        for ($i = 0; $i < $size; $i++) {
                            $this->_query_params[] = array($param[$i], 'default');
                            $parts[] = '?';
                        }
                        $result = join(', ', $parts);
                    }
                    break;
                case '':
                    $this->_query_params[] = array($param, 'default');
                    $result = '?';
                    break;
            }
        }
        return $result;
    }

    /**
     * Escape string
     *
     * @access private
     * @param string $string
     * @param bool $isIdent
     * @return string
     */
    private function _escape($string, $isIdent = false) {
        if (!$isIdent) {
            return $this->_pdo->quote($string);
        }
        else {
            return "`".str_replace('`', '``', $string)."`";
        }
    }

    /**
     * Transform result
     *
     * @access private
     * @param array $result
     * @param string $type
     * @return array
     */
    private function _transformResult($result, $type) {
        $buff = array();
        if ($type == 'ARRAY_KEY') {
            $size = sizeof($result);
            for ($i = 0; $i < $size; $i++) {
                $key = $result[$i]['ARRAY_KEY'];
                unset($result[$i]['ARRAY_KEY']);
                $buff[$key] = $result[$i];
            }
        }
        return $buff;
    }

    /**
     * Clean query params
     *
     * @access private
     * @return void
     */
    private function _cleanQueryParams() {
        $this->_queryRuntime('finish');
        $this->_query = null;
        $this->_query_args = array();
        $this->_query_params = array();
        $this->_query_transform = false;
    }

    /**
     * Set time mark for calculate query time
     *
     * @access private
     * @param string $action 'start' or 'finish'
     * @return void
     */
    private function _queryRuntime($action) {
        switch ($action) {
            case 'start':
                $this->_time = microtime(true);
                break;
            case 'finish':
                if (Config::get('profiler/level')) {
                    Profiler::add("DB query to {$this->_config['db']} - {$this->_query}", 'db-query', microtime(true) - $this->_time);
                }
                $this->_time = 0.00;
                break;
        }
    }
}