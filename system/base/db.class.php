<?php
/**
 * Db class file.
 *
 * Class for connecting to SQL databases and performing common operations.
 * This class based on PDO.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class Db {
    /**
     * @var static property to hold singleton instance
     */
    private static $_instance = NULL;
    /**
     * @var object $_pdo PDO object
     */
    private static $_pdo = NULL;
    /**
     * @var object $_sth PDOStatement object
     */
    private static $_sth = NULL;
    /**
     * @var string $_prefix table prefix
     */
    private static $_prefix = '';
    /**
     * @var array $_statistic db statistic
     */
    private static $_time;
    /**
     * @var string $_query query
     */
    private static $_query = NULL;
    /**
     * @var array $_query_args query arguments
     */
    private static $_query_args = array();
    /**
     * @var array $_query_params query parametrs
     */
    private static $_query_params = array();
    /**
     * @var bool $_query_transform
     */
    private static $_query_transform = false;

    /**
     * Connect to database
     *
     * @access public
     * @return void
     */
    public static function connect($server) {
        try {
            self::$_pdo = new PDO(Config::get("database/{$server}/driver").':host='.Config::get("database/{$server}/host").';dbname='.Config::get("database/{$server}/db").';charset='.Config::get("database/{$server}/charset"), Config::get("database/{$server}/user"), Config::get("database/{$server}/pass"));
            if (Config::get('debug/debug')) {
                self::$_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            self::$_prefix = Config::get("database/{$server}/prefix");
            if (Config::get('debug/profiler')) {
                Profiler::add("Db connected to {$server}", 'db-connect');
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
     * @return mixed array or NULL
     */
    public static function select() {
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $result = self::executeQuery($args);
            $result = self::$_sth->fetchAll(PDO::FETCH_ASSOC);
            if (preg_match('/ARRAY_KEY/six', self::$_query)) {
                $result = self::transformResult($result, 'ARRAY_KEY');
            }
            self::cleanQueryParams();
            return $result;
        }
        else {
            return NULL;
        }
    }

    /**
     * Select one row and return result
     *
     * @access public
     * @return mixed array or NULL
     */
    public static function selectRow() {
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $result = self::executeQuery($args);
            $result = self::$_sth->fetch(PDO::FETCH_ASSOC);
            // http://www.php.net/manual/en/pdostatement.fetch.php#74262
            self::$_sth->closeCursor();
            if (preg_match('/ARRAY_KEY/six', self::$_query)) {
                $result = self::transformResult($result, 'ARRAY_KEY');
            }
            self::cleanQueryParams();
            return $result;
        }
        else {
            return NULL;
        }
    }

    /**
     * Select one column and return result
     *
     * @access public
     * @return mixed array or NULL
     */
    public static function selectCol() {
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $result = self::executeQuery($args);
            $result = self::$_sth->fetchAll();
            $buff = array();
            $size = sizeof($result);
            if (preg_match('/ARRAY_KEY/six', self::$_query)) {
                for ($i = 0; $i < $size; $i++) {
                    $buff[$result[$i]['ARRAY_KEY']] = $result[$i][1];
                }
            }
            else {
                for ($i = 0; $i < $size; $i++) {
                    $buff[] = $result[$i][0];
                }
            }
            self::cleanQueryParams();
            return $buff;
        }
        else {
            return NULL;
        }
    }

    /**
     * Select scalar value and return result
     *
     * @access public
     * @return mixed or NULL
     */
    public static function selectCell() {
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $result = self::executeQuery($args);
            $result = self::$_sth->fetch(PDO::FETCH_NUM);
            // http://www.php.net/manual/en/pdostatement.fetch.php#74262
            self::$_sth->closeCursor();
            self::cleanQueryParams();
            return $result[0];
        }
        else {
            return NULL;
        }
    }

    /**
     * Execute query and return the result.
     *
     * @access public
     * @return mixed int or NULL
     */
    public static function query() {
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $result = self::executeQuery($args);
            if (preg_match('/^\s* INSERT \s+/six', self::$_query)) {
                $result = self::$_pdo->lastInsertId();
            }
            if (preg_match('/ARRAY_KEY/six', self::$_query)) {
                $result = self::transformResult($result, 'ARRAY_KEY');
            }
            self::cleanQueryParams();
            return $result;
        }
        else {
            return NULL;
        }
    }

    /**
     * Start transaction
     *
     * @access public
     * @return bool
     */
    public static function transaction() {
        return self::$_pdo->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * @access public
     * @return bool
     */
    public static function commit() {
        return self::$_pdo->commit();
    }

    /**
     * Rollback transaction
     *
     * @access public
     * @return bool
     */
    public static function rollback() {
        return self::$_pdo->rollBack();
    }

    /**
     * Prepare query for PDO execute.
     *
     * @param array $args arguments passed to the queries methods
     * @access private
     * @return void
     */
    private static function prepareQuery($args) {
        self::$_query = $args[0];
        self::$_query = str_replace('?_', self::$_prefix, self::$_query);
        self::$_query_args = array_slice($args, 1);

        $regexp = '{(\?)( [dsafn\#] ?)}sx';
        self::$_query = preg_replace_callback($regexp, array('self', 'expandPlaceholders'), self::$_query);
    }

    /**
     * Execute query and return result.
     *
     * @access private
     * @param array $args arguments passed to the queries methods
     * @return mixed
     */
    private static function executeQuery($args) {
        self::queryRuntime('start');
        self::prepareQuery($args);
        self::$_sth = self::$_pdo->prepare(self::$_query);
        $size = sizeof(self::$_query_params);
        $index = 1;
        for ($i = 0; $i < $size; $i++) {
            switch (self::$_query_params[$i][1]) {
                case 'int':
                    self::$_query_params[$i][0] = (int) self::$_query_params[$i][0];
                    self::$_sth->bindValue($index, self::$_query_params[$i][0], PDO::PARAM_INT);
                    break;
                case 'float':
                    self::$_query_params[$i][0] = (float) self::$_query_params[$i][0];
                    self::$_sth->bindValue($index, self::$_query_params[$i][0]);
                    break;
                case 'string':
                    self::$_query_params[$i][0] = (string) self::$_query_params[$i][0];
                    if (get_magic_quotes_gpc()) {
                        self::$_query_params[$i][0] = stripcslashes(self::$_query_params[$i][0]);
                    }
                    self::$_sth->bindValue($index, self::$_query_params[$i][0], PDO::PARAM_STR);
                    break;
                case 'default':
                    self::$_query_params[$i][0] = self::$_query_params[$i][0];
                    if (get_magic_quotes_gpc()) {
                        self::$_query_params[$i][0] = stripcslashes(self::$_query_params[$i][0]);
                    }
                    self::$_sth->bindValue($index, self::$_query_params[$i][0]);
                    break;
            }
            $index++;
        }
        try {
            $result = self::$_sth->execute();
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
    private static function expandPlaceholders($match) {
        $result = $match[0];
        if (!empty($match[0])) {
            $type = $match[2];
            if (!self::$_query_args)
                return 'DB_ERROR_NO_VALUE';
            $param = array_shift(self::$_query_args);
            switch ($type) {
                case 'd':
                    self::$_query_params[] = array($param, 'int');
                    $result = '?';
                    break;
                case 'f':
                    self::$_query_params[] = array($param, 'float');
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
                                self::$_query_params[] = array($v, 'default');
                                $k = self::escape($k, true);
                                $parts[] = "$k = ?";
                            }
                            else {
                                self::$_query_params[] = array($v, 'default');
                                $parts[] = '?';
                            }
                        }
                        $result = join(', ', $parts);
                    }
                    break;
                case 'n':
                    $param = (int) $param;
                    if ($param == 0) {
                        $result = 'NULL';
                    }
                    else {
                        self::$_query_params[] = array($param, 'int');
                        $result = '?';
                    }
                    break;
                case 's':
                    self::$_query_params[] = array($param, 'string');
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
                            self::$_query_params[] = array($param[$i], 'default');
                            $parts[] = '?';
                        }
                        $result = join(', ', $parts);
                    }
                    break;
                case '':
                    self::$_query_params[] = array($param, 'default');
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
    private static function escape($string, $isIdent = false) {
        if (!$isIdent) {
            return self::$_pdo->quote($string);
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
    private static function transformResult($result, $type) {
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
    private static function cleanQueryParams() {
        self::queryRuntime('finish');
        self::$_query = NULL;
        self::$_query_args = array();
        self::$_query_params = array();
        self::$_query_transform = false;
    }

    /**
     * Set time mark for calculate query time
     *
     * @access private
     * @param string $action 'start' or 'finish'
     * @return void
     */
    private static function queryRuntime($action) {
        switch ($action) {
            case 'start':
                self::$_time = microtime(true);
                break;
            case 'finish':
                if (Config::get('debug/profiler')) {
                    Profiler::add("DB query - ".self::$_query, 'db-query', microtime(true) - self::$_time);
                }
                self::$_time = 0.00;
                break;
        }
    }

    public function __construct() {
        
    }

    /**
     * Factory method to return the singleton instance
     *
     * @access public
     * @return Registry
     */
    public function getInstance() {
        if (NULL == Db::$_instance) {
            Db::$_instance = new Db;
        }
        return Db::$_instance;
    }
}
