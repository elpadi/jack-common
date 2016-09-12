<?php
namespace Jack;

use \Symfony\Component\Finder\Finder;

trait Router {

	protected function routeDefaults($route, $group) {
		if (!isset($route['method'])) $route['method'] = 'get';
		if (!isset($route['vars'])) $route['vars'] = array();
		$route['template'] = substr($group['path'], 1) . (!isset($route['path']) || strpos($route['path'], '{') !== FALSE ? (strlen($group['path']) > 1 ? '/' : '') . $route['name'] : $route['path']);
		if (!isset($route['path'])) $route['path'] = (strlen($group['path']) > 1 ? '/' : '') . $route['name'];
		return $route;
	}

	protected function loadRoutes($dir) {
		$routes = array();
		$finder = new Finder();
		$finder->files()->in($dir);
		foreach ($finder as $file) require($file->getRealpath());
		foreach ($routes as $group) {
			$this->_routeGroup = $group;
			$app_group = $this->_framework->group($group['path'], [$this, 'parseRouteGroup']);
			if (isset($group['middleware'])) foreach ($group['middleware'] as $fn) $app_group->add($fn);
		}
	}

	public function createAction($request, $response, $args=[]) {
		return new Action($request, $response, $args);
	}	
	
	public function parseRouteGroup() {
		$group = $this->_routeGroup;
		foreach ($group['routes'] as $route) {
			$route = $this->routeDefaults($route, $group);
			$app_route = call_user_func(
				[$this->_framework, $route['method']],
				$route['path'],
				isset($route['action']) && is_string($route['action']) ? function($request, $response) use ($route) {
					global $app;
					return $response->withRedirect($app->routeLookup($route['action'], isset($route['args']) ? $route['args'] : []));
				} : [$this, 'createAction']
			);
			if (isset($route['name'])) $app_route->setName($route['name']);
		}
	}

}
