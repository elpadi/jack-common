<?php
namespace Jack;

use \Symfony\Component\Finder\Finder;

class Router {

	protected static function routeDefaults($route, $group) {
		if (!isset($route['method'])) $route['method'] = 'get';
		if (!isset($route['args'])) $route['args'] = array();
		$route['template'] = substr($group['path'], 1) . (!isset($route['path']) || strpos($route['path'], '{') !== FALSE ? (strlen($group['path']) > 1 ? '/' : '') . $route['name'] : $route['path']);
		if (!isset($route['path'])) $route['path'] = (strlen($group['path']) > 1 ? '/' : '') . $route['name'];
		return $route;
	}

	public function loadRoutes($dir) {
		$routes = array();
		$finder = new Finder();
		$finder->files()->in($dir);
		foreach ($finder as $file) require($file->getRealpath());
		$this->routes = $routes;
	}

	public function enableRoutes() {
		$router = $this;
		foreach ($this->routes as $group) {
			$app_group = App::framework()->group($group['path'], \Closure::bind(function() use ($router, $group) { return $router->parseRouteGroup($group); }, $this));
			if (isset($group['middleware'])) foreach ($group['middleware'] as $fn) $app_group->add($fn);
		}
	}

	public function parseRouteGroup($group) {
		foreach ($group['routes'] as $route) {
			$route = $this->routeDefaults($route, $group);
			$app_route = call_user_func(
				[App::framework(), $route['method']],
				$route['path'],
				function($request, $response, $args) use ($route) { return Action::create($route)->run($request, $response, $args); }
			);
			if (isset($route['name'])) $app_route->setName($route['name']);
		}
	}

}
