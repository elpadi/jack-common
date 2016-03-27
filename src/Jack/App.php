<?php
namespace Jack;

class App {

	public static $framework;
	public static $assets;

	public static function init() {
		static::$assets = static::createAssetManager();
	}

	public static function routeLookUp($path, $placeholders=array()) {
		return self::$framework->getContainer()->get('router')->pathFor($path, $placeholders);
	}

	public static function notFound($response, $exception=null) {
		if (DEBUG && $exception) {
			var_dump(__FILE__.":".__LINE__." - ".__METHOD__, $exception);
			exit(0);
		}
		return $response->withStatus(404)->write("Not found.");
	}

	public static function notAuthorized($response) {
		return $response->withStatus(403)->write("Not authorized.");
	}

	public static function userCan($permission) {
		return true;
	}

	public static function createTemplate() {
		return new Template();
	}	
	
	public static function createAssetManager() {
		return new AssetManager();
	}	
	
	public static function render($path, $args=array()) {
		$template = static::createTemplate();
		return $template->render($path, $args);
	}	

	public static function initAssets() {
		$factory = new AssetFactory(APP_DIR.'/assets');
		$am = new AssetManager();
		$factory->setAssetManager($am);
		$factory->setDebug(DEBUG);
		$factory->addWorker(new CacheBustingWorker());
		self::$assets = $factory;
	}
	
}
