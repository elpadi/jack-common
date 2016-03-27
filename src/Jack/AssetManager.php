<?php
namespace Jack;

use Assetic\Factory\AssetFactory;
use Assetic\AssetWriter;
use Assetic\Factory\Worker\CacheBustingWorker;
use Assetic\AssetManager as AsseticManager;

abstract class AssetManager {

	abstract protected static function getAssetsDir();
	abstract protected static function getPublicDir();
	abstract protected static function getPublicPath();

	protected static $factory;

	public function __construct() {
		$factory = new AssetFactory(static::getAssetsDir());
		$am = new AsseticManager();
		$factory->setAssetManager($am);
		$factory->setDebug(DEBUG);
		$factory->addWorker(new CacheBustingWorker());
		static::$factory = $factory;
	}

	public static function url($path) {
		$asset_writer = new AssetWriter(static::getPublicDir());
		$asset = static::$factory->createAsset($path);
		$asset_writer->writeAsset($asset);
		return static::getPublicPath().'/'.$asset->getTargetPath();
	}

}
