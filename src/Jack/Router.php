<?php
namespace Jack;

use \Symfony\Component\Finder\Finder;

class Router {

	public $appClassName;

	public function __construct($routes_dir, $appClassName) {
		$this->appClassName = $appClassName;
		$this->loadRoutes($routes_dir, $appClassName);
	}

	public function routeDefaults($route, $group) {
		$appClassName = $this->appClassName;
		if (!isset($route['method'])) $route['method'] = 'get';
		if (!isset($route['vars'])) $route['vars'] = array();
		$route['template'] = substr($group['path'], 1) . (!isset($route['path']) || strpos($route['path'], '{') !== FALSE ? (strlen($group['path']) > 1 ? '/' : '') . $route['name'] : $route['path']);
		if (!isset($route['path'])) $route['path'] = (strlen($group['path']) > 1 ? '/' : '') . $route['name'];
		if (!isset($route['action'])) $route['action'] = function($request, $response, $args) use ($route, $appClassName) {
			return $response->write(call_user_func([$appClassName, 'render'],  $route['template'], is_callable($route['vars']) ? call_user_func($route['vars'], $args) : $route['vars']));
		};
		if (is_string($route['action'])) $route['action'] = function($request, $response, $args) use ($route, $appClassName) {
			return $response->withRedirect(call_user_func([$appClassName, 'routeLookup'], $route['action']));
		};
		return $route;
	}

	public function loadRoutes($dir, $appClassName) {
		$router = $this;
		$routes = array();
		$finder = new Finder();
		$finder->files()->in($dir);
		foreach ($finder as $file) require($file->getRealpath());
		foreach ($routes as $group) {
			$app_group = $appClassName::$framework->group($group['path'], function() use ($group, $router, $appClassName) {
				foreach ($group['routes'] as $route) {
					$route = $router->routeDefaults($route, $group);
					$app_route = call_user_func(array($appClassName::$framework, $route['method']), $route['path'], $route['action']);
					if (isset($route['name'])) $app_route->setName($route['name']);
				}
			});
			foreach ($group['middleware'] as $fn) $app_group->add($fn);
		}
	}

}
