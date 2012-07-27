<?php
/**
 * MainController class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.controller
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
require_once(Config::get('path/base').'controller.class.php');
class MainController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
    	//$this->load->extension('cache', 'cache', 'apc');

       // $user = $this->db->select('SELECT * FROM ?_webi LIMIT 0,2');
        $this->displayPage('pages/index.tpl');
    }
}
