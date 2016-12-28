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

	protected function assets() {
		return [
			'css' => ['pages/'.$this->route['name']],
			'js' => ['pages/'.$this->route['name']],
		];
	}

	protected function finalize($response) {
		return $response->write($this->render());
	}

	protected function api($response) {
		return $response->withHeader('Content-type', 'application/json')->write(json_encode($this->data));
	}

	public function run($request, $response, $args) {
		$this->fetchData($args, $request);
		if ($request->getContentType() === 'application/json') return $this->api($response);
		$this->data['assets'] = $this->assets();
		$this->data = array_merge(isset($this->data) ? $this->data : [], [
			'META_TITLE' => $this->metaTitle(),
			'META_DESCRIPTION' => $this->metaDescription(),
		]);
		return $this->finalize($response);
	}

	protected function fetchData($args) {
		$this->data = cockpit('collections:findOne', 'blocks', ['title' => ucwords($this->route['name'])]);
	}

}
