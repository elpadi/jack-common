<?php
namespace Jack\Action;

class Redirect {

	public function run($request, $response, $args) {
		global $app;
		return $response->withRedirect($app->routeLookup($this->route['action'], $args));
	}

}
