<?php
namespace Jack\Action;

class NotFound {

	public function run($request, $response, $args) {
		global $app;
		return $app->errorResponse($response, new \Exception('Page not found.', 404));
	}

}
