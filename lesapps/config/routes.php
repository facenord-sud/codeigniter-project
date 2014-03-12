<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$default_controller = "main";
$route['salut'] = 'asdas';
$route['default_controller'] = $default_controller;
$route['404_override'] = 'errors/error_404';

/*
|--------------------------------------------------------------------------
| GLOBAL ROUTES
|--------------------------------------------------------------------------
|
| We have to use a global variable to define routes.
| Write nothing in this file. To define routes, use the route language file.
|
*/
global $_ROUTE;

if(is_array($_ROUTE))
{
	$route = array_merge($route, $_ROUTE);
}

//// Yves : Pris sur http://www.web-and-development.com/codeigniter-minimize-url-and-remove-index-php/ 
//// Pour racourcir les urls de bases
//$language_alias = array('en','fr', 'dt', 'de');
//$controller_exceptions = array('admin','forums');
//
//$route["^(".implode('|', $language_alias).")/(".implode('|', $controller_exceptions).")(.*)"] = '$2';
//$route["^(".implode('|', $language_alias).")?/(.*)"] = $default_controller.'/$2';
//$route["^((?!\b".implode('\b|\b', $controller_exceptions)."\b).*)$"] = $default_controller.'/$1';
//foreach($language_alias as $language)
//	$route[$language] = $default_controller.'/index';

//print_r($route);

/* End of file routes.php */
/* Location: ./application/config/routes.php */