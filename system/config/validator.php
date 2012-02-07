<?php
/**
 * This file contains regexp rules for validation
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @package system.config
 * @license http://weegbo.com/license/
 * @since 0.8
 */
return array(
	'email'    => '^[a-zA-Z0-9._-]+@[a-zA-Z0-9_-]+[\.][a-zA-Z0-9._-]+$',
	'login'    => '^[a-zA-Z0-9._-]+$',
	'password' => '^[a-zA-Z0-9._-]+$',
	'numeric'  => '^[\d]+$',
	'filled'   => '^(.+)$'
);