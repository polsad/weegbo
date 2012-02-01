<?php
/**
 * Profiler class file.
 *
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
class Profiler {
    /**
     * @var static property to hold singleton instance
     */
    private static $_instance = NULL;
    /**
     *
     * @var static property for save configuration data
     */
    private static $_data = array();
    private static $_count = array();

    public static function add($message, $type = 'app', $time = NULL) {
        if ($time != NULL)
            $time = round($time, 4);
        self::$_data[] = array(round(microtime(true) - START_TIME, 4), round(memory_get_usage() / 1024, 2), $message, $time);
        self::$_count[$type] += 1;
    }

    public static function showResult() {
        $time = round(microtime(true) - START_TIME, 4);
        $memory = round(memory_get_usage() / 1024, 2);
        $counts = array();
        foreach (self::$_count as $k => $v) {
            $val = (int)$v;
            switch ($k) {
                case 'load-controller':
                    $counts[0]['load-controller'] = ($val > 1) ? "{$val} controllers" : "{$val} controller";
                break;
                case 'load-extension':
                    $counts[0]['load-extension'] = ($val > 1) ? "{$val} extenstions" : "{$val} extenstion";
                break;
                case 'load-helper':
                    $counts[0]['load-helper'] = ($val > 1) ? "{$val} helpers" : "{$val} helper";
                break;            
                case 'load-model':
                    $counts[0]['load-model'] = ($val > 1) ? "{$val} models" : "{$val} model";
                break;
                case 'load-library':
                    $counts[0]['load-library'] = ($val > 1) ? "{$val} libraries" : "{$val} library";
                break;            
                case 'db-connect':
                    $counts[1]['db-connect'] = ($val > 1) ? "{$val} connections" : "{$val} connection";
                break;     
                case 'db-query':
                    $counts[1]['db-query'] = ($val > 1) ? "{$val} queries" : "{$val} query";
                break;            
            }
        }
        $counts[0] = join(', ', (array)$counts[0]);
        $counts[1] = join(', ', (array)$counts[1]);

        $result = array();
        $result[] = "Total time {$time} sec. Memory {$memory} Kb.";
        $result[] = ($counts[0] != NULL) ? "Loaded: {$counts[0]}. " : "";
        $result[] = ($counts[1] != NULL) ? "Database: {$counts[1]}. " : "";            
        if (Config::get('debug/profiler-trace')) {
            $result[] = "Trace:";
            foreach (self::$_data as $k => $v) {
                $result[] = ($v[3] == '') ? "{$v[0]} sec, {$v[1]} Kb - {$v[2]}" : "{$v[0]} sec, {$v[1]} Kb - {$v[2]} ({$v[3]} sec)";
            }
        }
        echo $result = join('<br>', (array)$result);
    }

    /**
     * Factory method to return the singleton instance.
     *
     * @access public
     * @return object
     */
    public static function getInstance() {
        if (NULL == Profiler::$_instance) {
            Profiler::$_instance = new Profiler;
        }
        return Profiler::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        
    }
}
