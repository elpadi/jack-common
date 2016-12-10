<?php
namespace Jack\Action;

class Page {

	protected $data;

	public function __construct($route) {
		$this->route = $route;
	}

	protected function metaTitle() {
		return sprintf('%s | %s', $this->route['name'] === 'intro' ? 'Welcome' : ucwords($this->route['name']), 'Jack Magazine');
	}

	protected function metaDescription() {
		return cockpit('collections:findOne', 'pagedescriptions', ['name' => $this->route['name']]);
	}

	protected function templatePath() {
		return 'default';
	}

	protected function render() {
		global $app;
		return $app->render($this->templatePath(), $this->data);
	}

	protected static function notFound($response) {
		global $app;
		return $app->notFound($response);
	}

	protected function finalize($response) {
		return $response->write($this->render());
	}

	public function run($request, $response, $args) {
		$this->fetchData($args, $request);
		$this->data = array_merge($this->data, [
			'META_TITLE' => $this->metaTitle(),
			'META_DESCRIPTION' => $this->metaDescription(),
		]);
		return $this->finalize($response);
	}

	protected function fetchData($args) {
		$this->data = cockpit('collections:findOne', 'blocks', ['title' => ucwords($this->route['name'])]);
	}

}
