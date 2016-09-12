<?php
namespace Jack;

class Action {

	public function __construct($request, $response, $args) {
		$name = $request->getAttribute('route')->getName();
		return call_user_func($this->getActionCallback($name), $request, $response, $args, $name);
	}

	protected function getActionCallback($name) {
		$fn = [$this, str_replace('/', '_', $name)];
		return is_callable($fn) ? $fn : [$this, '_default'];
	}
	
	public function _default($request, $response, $args, $name="default") {
		global $app;
		return $response->write($app->render($name, $args));
	}

}
