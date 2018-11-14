<?php
namespace Jack\Images;

use Functional as F;
use Symfony\Component\Cache\Adapter\ProxyAdapter;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class Manager {

	public $metaCache;

	public function __construct() {
		$this->metaCache = new ProxyAdapter(new \Jack\Cache\SingleJsonPool('image-meta'));
	}

	public static function filePath($path) {
		return realpath(PUBLIC_ROOT_DIR.$path);
	}

	public static function filePathToUrl($path) {
		return str_replace(PUBLIC_ROOT_DIR, '', $path);
	}

	public static function hashToPath($hash, $extension) {
		return sprintf('%s/%s/%s.%s', PUBLIC_ROOT_DIR, 'assets/cache/image-variants', $hash, $extension);
	}

	public static function generateHash($path, $width, $height) {
		return md5(serialize([$path, $width, $height]));
	}

	protected function resizedPath($image, $dims) {
		$filePath = static::hashToPath(static::generateHash($image->path, $dims->getWidth(), $dims->getHeight()), F\last(explode('.', $image->path)));
		if (!file_exists($filePath)) {
			$variant = new Variant($image, $dims);
			$variant->save($filePath);
		}
		return $filePath;
	}

	public function getMediumSize($path) {
		$image = $this->createImage($path);
		$dims = $image->resizedDims('medium');
		return sprintf('%dx%d', $dims->getWidth(), $dims->getHeight());
	}

	public function imageUrl($path, $size='medium') {
		$image = $this->createImage($path);
		return static::filePathToUrl($this->resizedPath($image, $image->resizedDims($size)));
	}

	public function responsiveImageSrcset($path, $sizes=array()) {
		if (empty($sizes)) $sizes = ['large','xl'];
		$image = $this->createImage($path);
		foreach ($sizes as $i => $size) {
			$dims = $image->resizedDims($size);
			$srcset[] = sprintf('%s %dx', static::filePathToUrl($this->resizedPath($image, $dims)), $i + 2);
		}
		return implode(', ', $srcset);
	}

	public function getImageMeta($path) {
			$image = (new Imagine())->open(static::filePath($path));
			$dims = $image->getSize();
			$image = null;
			$meta['width'] = $dims->getWidth();
			$meta['height'] = $dims->getHeight();
			return $meta;
	}

	protected function createImage($path) {
		$image = new Image($path);
		$cacheKey = md5($path);
		$cacheItem = $this->metaCache->getItem($cacheKey);
		if ($cacheItem->isHit()) $image->setMeta($cacheItem->get());
		else {
			$meta = $this->getImageMeta($path);
			$cacheItem->set($meta);
			$this->metaCache->save($cacheItem);
			$image->setMeta($meta);
		}
		return $image;
	}

}
