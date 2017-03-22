<?php
namespace Jack;

abstract class App {

	use Jack;

	protected $_framework;
	protected $_assets;
	protected $_router;
	public $templateManager;

	public function __construct() {
		clearstatcache();
		$this->_assets = static::createAssetManager();
		$this->_router = new Router();
		$this->imageManager = new Images\Manager();
		$this->templateManager = static::createTemplate();
	}

	public static function instance() {
		global $app;
		if ($app) return $app;
		throw new \BadMethodCallException("Instance does not exist.");
	}

	public static function namespaces() {
		global $app;
		$parts = explode('\\', get_class($app));
		return [$parts[0], 'Jack'];
	}

	public static function framework() {
		global $app;
		return $app->_framework;
	}

	public function run() {
		static::framework()->run();
	}

	public function render($path, $args=array()) {
		return $this->templateManager->render($path, $args);
	}	

	public function routeLookUp($path, $placeholders=array()) {
		return static::framework()->getContainer()->get('router')->pathFor($path, $placeholders);
	}

	public static function createTemplate() {
		return new Template();
	}	
	
	public static function createAssetManager() {
		return new AssetManager();
	}	
	
	public function assetUrl($path) {
		return $this->_assets->url($path);
	}

	public function url($path) {
		return sprintf('%s/%s', PUBLIC_ROOT === '/' ? '' : PUBLIC_ROOT, $path);
	}

	public function urlToSource($url_path) {
		return file_get_contents(PUBLIC_ROOT_DIR.$url_path);
	}

	public function errorResponse($response, $exception) {
		return $response->withStatus($exception->getCode())->write($this->render('default', ['content' => sprintf('<h2>%d</h2><p>%s</p>', $exception->getCode(), $exception->getMessage())]));
	}

	public function notAuthorized($response) {
		return $response->withStatus(403)->write("Not authorized.");
	}

	public static function userCan($permission) {
		return true;
	}

}
