<?php
namespace Jack\Action;

class Redirect {

	public function __construct($route) {
		$this->route = $route;
	}

	public function run($request, $response, $args) {
		global $app;
		return $response->withRedirect($app->routeLookup($this->route['action'], array_merge($this->route['args'], $args)));
	}

}
