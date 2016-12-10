<?php
namespace Jack;

abstract class AssetManager {

	protected static function getAssetsDir() {}
	protected static function getPublicDir() {}
	protected static function getPublicPath() {}

	protected static $factory;

	public function __construct() {
	}

	public static function url($path) {
		return sprintf('%s/%s', static::getPublicPath(), $path);
	}

}
