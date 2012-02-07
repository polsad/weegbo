<?php
/**
 * Controller class file.
 *
 * Родительский класс для контроллеров.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @package system.base
 * @since 0.8
 */
abstract class Controller {

    public function __construct() {

    }

    /**
     * Return value from Registry by name
     *
     * @access public
     * @param string $var object's name in Registry
     * @return mixed
     */
    public function __get($var) {
        return Registry::get($var);
    }

    /**
     * Called controller's method 
     *
     * @access public
     * @return void
     */
    public final function execute($action) {
        /**
         * Check action
         */
        if (!method_exists($this, $action)) {
            if (Config::get('app/router')) {
                $this->displayErrorPage(404);
            }
            else {
                $action = 'index';
            }
        }
        $this->$action();
    }

    /**
     * Display page.
     *
     * @access public
     * @param string $page template name
     * @return void
     */
    public final function displayPage($page, $expire = NULL) {
        $this->view->display($page, $expire);
        /**
         * If statistic enable, and page is render, show statistic
         */
        if (Config::get('debug/profiler')) {
            Profiler::showResult();
        }
        exit();
    }

    /**
     * Display error page.
     *
     * @access public
     * @param int $error error number, see Error::displayErrorPage()
     * @return void
     */
    public final function displayErrorPage($error) {
        Error::displayErrorPage($error);
        /**
         * If statistic enable, and page is render, show statistic
         */
        if (Config::get('debug/profiler')) {
            Profiler::showResult();
        }
        exit();
    }

    /**
     * Redirect on URL.
     *
     * @access public
     * @param string $page URL ('/' -  main page)
     * @return void
     */
    public final function redirect($url) {
        if (!preg_match('#^(https?|ftp)://#', $url, $match)) {
            $url = Config::get('path/host') . ltrim($url, '/');
        }
        Header('Location: ' . $url);
        exit();
    }

    /**
     * Abstract method index.
     */
    abstract protected function index();
}