<?php
namespace Jack;

abstract class App {

	use Jack;
	use Router;

	protected $_framework;
	protected $_assets;

	public function __construct() {
		$this->_assets = static::createAssetManager();
	}

	public function run() {
		$this->_framework->run();
	}

	public function render($path, $args=array()) {
		$template = static::createTemplate();
		return $template->render($path, $args);
	}	

	public function routeLookUp($path, $placeholders=array()) {
		return $this->_framework->getContainer()->get('router')->pathFor($path, $placeholders);
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

	public function notFound($response, $exception=null) {
		if (DEBUG && $exception) {
			var_dump(__FILE__.":".__LINE__." - ".__METHOD__, $exception);
			exit(0);
		}
		return $response->withStatus(404)->write("Not found.");
	}

	public function notAuthorized($response) {
		return $response->withStatus(403)->write("Not authorized.");
	}

	public static function userCan($permission) {
		return true;
	}

}
