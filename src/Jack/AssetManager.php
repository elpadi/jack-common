<?php
namespace Jack;

abstract class AssetManager {

	abstract protected static function getAssetsDir();
	abstract protected static function getPublicDir();
	abstract protected static function getPublicPath();

	protected static $factory;

	public function __construct() {
	}

	public static function url($path) {
		return sprintf('%s/%s', static::getPublicPath(), $path);
	}

}
