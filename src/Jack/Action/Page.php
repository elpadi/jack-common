<?php
namespace Jack\Action;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Jack\App;

class Page {

	use ActionTrait;

	protected $shortcodes;

	protected function metaTitle() {
		$title = sprintf('%s | %s', isset($this->data['title']) ? $this->data['title'] : ucwords($this->route['name']), 'Jack Magazine');
		return $title;
	}

	protected function metaDescription() {
		return isset($this->data['description']) ? $this->data['description'] : '';
	}

	protected function graphTags() {
		return [
			'OPEN_GRAPH' => [
				'title' => str_replace(' | Jack Magazine', '', $this->metaTitle()),
				'description' => $this->metaDescription(),
				'image' => '',
				'type' => 'website',
				'url' => App::canonicalUrl(),
			],
			'TWITTER_CARD' => [
				'card' => 'summary',
				'site' => '@thejackmag',
				'creator' => '@thejackmag',
			],
		];
	}

	protected function templatePath() {
		$path = "pages/{$this->route['name']}";
		return App::$container['templates']->exists($path) ? $path : 'default';
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

	public function responsiveImageShortcode(ShortcodeInterface $s) {
		global $app;
		return sprintf('<img src="%s" srcset="%s" sizes="100vw" alt="">',
			$app->imageManager->imageUrl($app->assetUrl($s->getParameter('path')), 'medium'),
			$app->imageManager->responsiveImageSrcset($app->assetUrl($s->getParameter('path')), ['medium','large','double'])
		);
	}

	protected function fetchPageData() {
		$data = cockpit('collections:findOne', 'pages', ['path' => $_SERVER['REQUEST_URI']]);
		if ($data) {
			$this->data = array_merge($this->data, $data);
		}
	}

	protected function fetchData($args) {
		$this->fetchPageData();
	}

	protected function page($request, $response, $args) {
		global $app;
		$this->shortcodes->addHandler('resp_image', [$this, 'responsiveImageShortcode']);
		try {
			$this->fetchData($args, $request);
			if (isset($this->data['content'])) {
				$this->data['content'] = $this->shortcodes->process($this->data['content']);
			}
		}
		catch (\Exception $e) {
			if (DEBUG) App::debugError($e);
		}
		if (strpos($request->getHeaderLine('accept'), 'application/json') === 0) return $this->api($response);
		$this->data['assets'] = $this->assets();
		$this->data = array_merge(isset($this->data) ? $this->data : [], [
			'META_TITLE' => $this->metaTitle(),
			'META_DESCRIPTION' => $this->metaDescription(),
			'CANONICAL_URL' => App::canonicalUrl(),
			'ACTION_CLASS' => get_class($this),
			'GRAPH_TAGS' => $this->graphTags(),
		]);
		return $this->finalize($response);
	}

}
