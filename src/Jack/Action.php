<?php
namespace Jack;

class Action {

	public function __construct($request, $response, $args) {
		$name = $request->getAttribute('route')->getName();
		$fn = [$this, str_replace('/', '_', $name)];
		return call_user_func(is_callable($fn) ? $fn : [$this, '_default'], $request, $response, $args, $name);
	}
	
	public function _default($request, $response, $args, $name) {
		global $app;
		return $response->write($app->render($name, $args));
	}

}
