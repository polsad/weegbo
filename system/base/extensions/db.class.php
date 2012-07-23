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
     * @param string $connect connection name in daatabase config
     * @return void
     */
    
    public function __construct($connect) {
        try {
            // If not loaded, load database config
            if (Config::get('database', true) == null) {
                Config::load(Config::get('config/database'));
            }
            $this->_pdo = new PDO(Config::get("database/{$connect}/driver").':host='.Config::get("database/{$connect}/host").';dbname='.Config::get("database/{$connect}/db").';charset='.Config::get("database/{$connect}/charset"), Config::get("database/{$connect}/user"), Config::get("database/{$connect}/pass"));
            if (Config::get('debug/debug')) {
                $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            $this->_prefix = Config::get("database/{$connect}/prefix");
            if (Config::get('debug/profiler')) {
                Profiler::add("Db connected to {$connect}", 'db-connect');
            }
        }
        catch (PDOException $e) {
            throw new CException($e->getMessage(), 1, 500);
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
            $result = $this->executeQuery($args);
            $result = $this->_sth->fetchAll(PDO::FETCH_ASSOC);
            if (preg_match('/ARRAY_KEY/six', $this->_query)) {
                $result = $this->transformResult($result, 'ARRAY_KEY');
            }
            $this->cleanQueryParams();
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
            $result = $this->executeQuery($args);
            $result = $this->_sth->fetch(PDO::FETCH_ASSOC);
            // http://www.php.net/manual/en/pdostatement.fetch.php#74262
            $this->_sth->closeCursor();
            if (preg_match('/ARRAY_KEY/six', $this->_query)) {
                $result = $this->transformResult($result, 'ARRAY_KEY');
            }
            $this->cleanQueryParams();
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
            $result = $this->executeQuery($args);
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
            $this->cleanQueryParams();
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
            $result = $this->executeQuery($args);
            $result = $this->_sth->fetch(PDO::FETCH_NUM);
            // http://www.php.net/manual/en/pdostatement.fetch.php#74262
            $this->_sth->closeCursor();
            $this->cleanQueryParams();
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
            $result = $this->executeQuery($args);
            if (preg_match('/^\s* INSERT \s+/six', $this->_query)) {
                $result = $this->_pdo->lastInsertId();
            }
            if (preg_match('/ARRAY_KEY/six', $this->_query)) {
                $result = $this->transformResult($result, 'ARRAY_KEY');
            }
            $this->cleanQueryParams();
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

    /**
     * Prepare query for PDO execute.
     *
     * @param array $args arguments passed to the queries methods
     * @access private
     * @return void
     */
    private function prepareQuery($args) {
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
    private function executeQuery($args) {
        $this->queryRuntime('start');
        $this->prepareQuery($args);
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
            throw new CException($e->getMessage(), 1, 500);
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
    private function expandPlaceholders($match) {
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
                                $k = $this->escape($k, true);
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
    private function escape($string, $isIdent = false) {
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
    private function transformResult($result, $type) {
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
    private function cleanQueryParams() {
        $this->queryRuntime('finish');
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
    private function queryRuntime($action) {
        switch ($action) {
            case 'start':
                $this->_time = microtime(true);
                break;
            case 'finish':
                if (Config::get('debug/profiler')) {
                    Profiler::add("DB query to ".$this->_connect." - ".$this->_query, 'db-query', microtime(true) - $this->_time);
                }
                $this->_time = 0.00;
                break;
        }
    }

}