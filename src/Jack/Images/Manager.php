<?php
namespace Jack\Images;

use Functional as F;
use Symfony\Component\Cache\Adapter\ProxyAdapter;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class Manager {

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

	protected function resizedPath($image, $dims) {
		$filePath = static::hashToPath(md5(serialize([$image->path, $dims->getWidth(), $dims->getHeight()])), F\last(explode('.', $image->path)));
		if (!file_exists($filePath)) {
			$variant = new Variant($image, $dims);
			$variant->save($filePath);
		}
		return $filePath;
	}

	public function imageUrl($path, $size='small') {
		$image = $this->createImage($path);
		return static::filePathToUrl($this->resizedPath($image, $image->resizedDims($size)));
	}

	public function responsiveImageSrcset($path, $sizes=array()) {
		if (empty($sizes)) $sizes = ['small','medium','large'];
		$image = $this->createImage($path);
		foreach ($sizes as $size) {
			$dims = $image->resizedDims($size);
			$srcset[] = sprintf('%s %dw', static::filePathToUrl($this->resizedPath($image, $dims)), $dims->getWidth());
		}
		return implode(', ', $srcset);
	}

	protected function getImageMeta($path) {
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
