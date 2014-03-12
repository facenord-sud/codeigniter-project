<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Hook qui s'occupe du routage en fonction du fichier de config
 * routing.php
 *
 * @package default
 * @author 
 **/
class RoutingHook  {

	/**
	 * permet de créer les routes définies dans le fichiers routing.php
	 * les stoques dans une variables glaobales $_ROUTING
	 * ces routes seront utilisées dans la méthode set_route du hook multilingual.php
	 *
	 * @return void
	 * @author Numa de Montmollin
	 **/
	public function makeRouting() {

		require_once APPPATH.'config/routing.php';
		$routing = $config['routing'];
		$routes = array();
		global $_ROUTING;
		foreach ($routing as $key => $route) {
			if(empty($route['prefix'])) {
				$route['prefix'] = $route['controller_name'];
			}
			// $routes[$route['controller_name']] = array($route['pattern'] => $route['controller_name']);
			foreach ($route['methods'] as $key => $method) {
				if(empty($method['pattern'])) {
					$method['pattern'] = $method['method'];
				}
				if($method['method']=='index') {
					$routes[$route['controller_name'].'_'.$method['method'].'3'] = 
					array($route['prefix'].'/(:num)'=>$route['controller_name'].'/'.$method['method'].'/$1');
					$routes[$route['controller_name'].'_'.$method['method'].'1'] = 
					array($route['prefix'] => $route['controller_name'].'/'.$method['method']);
					continue;
				}
				// $routes[$route['controller_name'].'_'.$method['method'].'3'] = 
				// array($route['pattern'].'/'.$method['pattern'].'/(:any)/(:any)' => $route['controller_name'].'/'.$method['method'].'/$1/$2');
				$routes[$route['controller_name'].'_'.$method['method'].'2'] = 
				array($route['prefix'].'/'.$method['pattern'].'/(:any)' => $route['controller_name'].'/'.$method['method'].'/$1');
				$routes[$route['controller_name'].'_'.$method['method'].'1'] = 
				array($route['prefix'].'/'.$method['pattern'] => $route['controller_name'].'/'.$method['method']);
				
				
			}
			
		}
		$_ROUTING['route'] = $routes;
		// echo "<pre>";
		// print_r($routes);
		// echo "</pre>";
	}
} // END class RoutingHook 