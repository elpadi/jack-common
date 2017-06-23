<?php
namespace Jack\Action;

use Thunder\Shortcode\ShortcodeFacade;

trait ActionTrait {

	protected $data;
	protected $method;

	public function __construct($route, $method='page') {
		$this->route = $route;
		$this->method = $method;
		$this->data = [];
		$this->shortcodes = new ShortcodeFacade();
	}

	public function run($request, $response, $args) {
		$method = $this->method;
		$this->$method($request, $response, $args);
	}

	protected function api($response) {
		return $response->withHeader('Content-type', 'application/json')->write(json_encode($this->data));
	}

}
