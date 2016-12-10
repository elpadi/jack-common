<?php
namespace Jack\Action;

class NotFound {

	public function run($request, $response, $args) {
		global $app;
		return $app->notFound($response);
	}

}
