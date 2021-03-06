<?php
namespace Jack;

abstract class AssetManager {

	protected static function getAssetsDir() {}
	protected static function getPublicDir() {}
	protected static function getPublicPath() {}

	protected static $factory;

	public function __construct() {
	}

	public static function background($name) {
		$url = App::instance()->assetUrl("backgrounds/$name.jpg");
		return [
			'src' => App::instance()->imageManager->imageUrl($url, 'large'),
			'srcset' => App::instance()->imageManager->responsiveImageSrcset($url, ['large','double']),
		];
		return sprintf('%s/%s', static::getPublicPath(), $path);
	}

	public static function path($path='') {
		return sprintf('%s/%s', static::getPublicDir(), $path);
	}

	public static function url($path='') {
		return sprintf('%s/%s', static::getPublicPath(), $path);
	}

	public static function css($paths, $prefix='src/css', $media='all') {
		global $app;
		if (!DEBUG) {
			// Implement post-processing
		}
		return implode(' ', array_map(function($path) use ($prefix, $media, $app) {
			return sprintf('<link rel="stylesheet" href="%s" media="%s">', $app->assetUrl(sprintf('%s/%s.css?v=%d', $prefix, $path, getenv('CSS_VERSION'))), $media);
		}, $paths));
	}

	public static function js($paths, $prefix='src/js') {
		global $app;
		if (!DEBUG) {
			// Implement post-processing
		}
		return implode(' ', array_map(function($path) use ($app, $prefix) {
			return sprintf('<script src="%s"></script>', $app->assetUrl(sprintf('%s/%s.js?v=%d', $prefix, $path, getenv('JS_VERSION'))));
		}, $paths));
	}

}
