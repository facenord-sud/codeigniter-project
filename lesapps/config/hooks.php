<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['pre_system'][] = array(
	'class'		=> 'RoutingHook',
	'function'	=> 'makeRouting',
	'filename'	=> 'RoutingHook.php',
	'filepath'	=> 'hooks'
);
$hook['pre_system'][] = array(
	'class'		=> 'Multilingual',
	'function'	=> 'set_route',
	'filename'	=> 'multilingual.php',
	'filepath'	=> 'hooks'
);

$hook['pre_controller'][] = array(
	'class'		=> 'Multilingual',
	'function'	=> 'set_language',
	'filename'	=> 'multilingual.php',
	'filepath'	=> 'hooks'
);
$hook['post_controller_constructor'][] = array(
	'class'		=> 'Multilingual',
	'function'	=> 'redirect_route',
	'filename'	=> 'multilingual.php',
	'filepath'	=> 'hooks'
);

$hook['post_controller_constructor'][] = array(
	'class'		=> 'Firewall',
	'function'	=> 'start',
	'filename'	=> 'firewall.php',
	'filepath'	=> 'hooks'
);

$hook['post_controller_constructor'][] = array(
    'class' => 'TwiggyView',
    'function' => 'loadLanguage',
    'filename' => 'TwiggyView.php',
    'filepath' => 'hooks',
);

$hook['post_controller'][] = array(
    'class' => 'TwiggyView',
    'function' => 'display',
    'filename' => 'TwiggyView.php',
    'filepath' => 'hooks',
);


/* End of file hooks.php */
/* Location: ./application/config/hooks.php */