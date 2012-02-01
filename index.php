<?php
/**
 * Weegbo bootstrap file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
/*
 * Define basic constant
 *
 * PATH_ROOT - path to application directory
 * PATH_BASE - path to framework base files
 */
define('START_TIME', microtime(true));
define('PATH_ROOT', dirname(__file__).'/');
define('PATH_BASE', PATH_ROOT.'system/base/');

/**
 * Run application. PATH_ROOT.'system/' - path to application folder
 */
require_once(PATH_BASE.'base.class.php');
Base::createWebApplication(PATH_ROOT.'system/');