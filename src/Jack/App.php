<?php
namespace Jack;

use Symfony\Component\Debug\ExceptionHandler;

abstract class App {

	use Jack;

	protected $_framework;
	protected $_assets;
	protected $_router;
	public $templateManager;
	public static $container;

	public function __construct() {
		clearstatcache();
		$this->_assets = static::createAssetManager();
		$this->_router = new Router();
		$this->imageManager = new Images\Manager();
		$this->templateManager = static::createTemplate();
		$container = new \Pimple\Container();
		$container['session'] = function() {
			$session_factory = new \Aura\Session\SessionFactory;
			$session = $session_factory->newInstance($_COOKIE);
			$session->setCookieParams(['lifetime' => 3600 * 24 * 365]);
			return $session;
		};
		$container['events'] = function() {
			return new \Symfony\Component\EventDispatcher\EventDispatcher();
		};
		$container['images'] = function() {
			return new Images\Manager();
		};
		$container['logger'] = function() {
			return new Logging\Logger();
		};
		$container['assets'] = function() {
			return static::createAssetManager();
		};
		$container['templates'] = function() {
			return static::createTemplate();
		};
		static::$container = $container;
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
		return static::$container['templates']->render($path, $args);
	}	

	public function routeLookUp($path, $placeholders=array()) {
		try {
			$url = static::framework()->getContainer()->get('router')->pathFor($path, $placeholders);
		}
		catch (\Exception $e) {
			return '';
		}
		return $url;
	}

	public static function routeUrl($path, $placeholders=array()) {
		global $app;
		return $app->routeLookUp($path, $placeholders);
	}

	public static function redirect($url) {
		$canonical = static::canonicalUrl($url);
		header("Location: $url");
		exit();
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

	public function appendModifiedTime($url) {
		if ($parts = parse_url($url)) {
			$fp = PUBLIC_ROOT_DIR.'/'.$parts['path'];
			if ($t = filemtime($fp)) {
				return $url.(empty($parts['query']) ? '?' : '&')."mtime=$t";
			}
		}
		return $url;
	}

	public static function url($path) {
		return sprintf('%s/%s', PUBLIC_ROOT === '/' ? '' : PUBLIC_ROOT, $path);
	}

	public static function canonicalUrl($uri='') {
		return sprintf('%s://%s%s', isset($_SERVER['HTTPS']) ? "https" : "http", $_SERVER['HTTP_HOST'], $uri ? $uri : $_SERVER['REQUEST_URI']);
	}

	public function urlToSource($url_path) {
		return file_get_contents(PUBLIC_ROOT_DIR.$url_path);
	}

	public function errorResponse($response, $exception) {
		if (DEBUG) {
			dump(__FILE__.":".__LINE__." - ".__METHOD__, $exception, $exception->getPrevious());
			exit(0);
		}
		return $response->withStatus($exception->getCode())->write($this->render('default', ['content' => sprintf('<h2>%d</h2><p>%s</p>', $exception->getCode(), $exception->getMessage())]));
	}

	public static function debugError($exception) {
		$handler = new ExceptionHandler();
		$handler->sendPhpResponse($exception);
		exit();
	}

	public function notAuthorized($response) {
		return $response->withStatus(403)->write("Not authorized.");
	}

	public static function userCan($permission) {
		return true;
	}

}
