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
		$entry = cockpit('collections:findOne', 'pagedescriptions', ['name' => $this->route['name']]);
		return $entry ? $entry['content'] : '';
	}

	protected function canonicalUrl($uri='') {
		return sprintf('%s://%s%s', isset($_SERVER['HTTPS']) ? "https" : "http", $_SERVER['HTTP_HOST'], $uri ? $uri : $_SERVER['REQUEST_URI']);
	}

	protected function graphTags() {
		return [
			'OPEN_GRAPH' => [
				'title' => str_replace(' | Jack Magazine', '', $this->metaTitle()),
				'description' => $this->metaDescription(),
				'image' => '',
				'type' => 'website',
				'url' => $this->canonicalUrl(),
			],
			'TWITTER_CARD' => [
				'card' => 'summary',
				'site' => '@thejackmag',
				'creator' => '@thejackmag',
			],
		];
	}

	protected function templatePath() {
		global $app;
		$path = "pages/{$this->route['name']}";
		return $app->templateManager->exists($path) ? $path : 'default';
	}

	protected function render() {
		global $app;
		return $app->render($this->templatePath(), $this->data);
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
		global $app;
		try {
			$this->fetchData($args, $request);
		}
		catch (\Exception $e) {
			if (DEBUG) {
				var_dump(__FILE__.":".__LINE__." - ".__METHOD__, $e);
				exit(0);
			}
			return $app->errorResponse($response, $e);
		}
		if ($request->getContentType() === 'application/json') return $this->api($response);
		$this->data['assets'] = $this->assets();
		$this->data = array_merge(isset($this->data) ? $this->data : [], [
			'META_TITLE' => $this->metaTitle(),
			'META_DESCRIPTION' => $this->metaDescription(),
			'CANONICAL_URL' => $this->canonicalUrl(),
			'GRAPH_TAGS' => $this->graphTags(),
		]);
		return $this->finalize($response);
	}

	protected function fetchData($args) {
		$this->data = cockpit('collections:findOne', 'blocks', ['title' => ucwords($this->route['name'])]);
	}

}
