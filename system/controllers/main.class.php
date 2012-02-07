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
require_once(PATH_BASE.'controller.class.php');
class MainController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->displayPage('pages/index.tpl');
    }

    /**
     * Captcha
     */
    public function captcha() {
        $this->load->extension('captcha');
        $this->captcha->getCaptchaImage();
    }
}
