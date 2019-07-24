<?php
namespace Jack\Images;

use Functional as F;
use Symfony\Component\Cache\Adapter\ProxyAdapter;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class Manager {

	public $metaCache;
	public $isResizingEnabled;

	public function __construct() {
		$this->isResizingEnabled = !IS_LOCAL || isset($_ENV['TEST_IMAGE_RESIZER']);
		if ($this->isResizingEnabled) $this->metaCache = new ProxyAdapter(new \Jack\Cache\SingleJsonPool('image-meta'));
	}

	public static function filePath($url) {
		return realpath(PUBLIC_ROOT_DIR.$url);
	}

	public static function filePathToUrl($path) {
		return str_replace(PUBLIC_ROOT_DIR, '', $path);
	}

	public static function hashToPath($hash, $extension) {
		return sprintf('%s/%s/%s.%s', PUBLIC_ROOT_DIR, 'assets/cache/image-variants', $hash, $extension);
	}

	public static function generateHash($url, $width, $height) {
		return md5(serialize([$url, $width, $height]));
	}

	protected function resizedPath($image, $dims) {
		$filePath = static::hashToPath(static::generateHash($image->path, $dims->getWidth(), $dims->getHeight()), F\last(explode('.', $image->path)));
		if (!file_exists($filePath)) {
			$variant = new Variant($image, $dims);
			$variant->save($filePath);
		}
		return $filePath;
	}

	public function getMediumSize($url) {
		if ($this->isResizingEnabled) {
			$image = $this->createImage($url);
			$dims = $image->resizedDims('medium');
			return sprintf('%dx%d', $dims->getWidth(), $dims->getHeight());
		}
	}

	public function imageUrl($url, $size='large') {
		if ($this->isResizingEnabled) {
			$image = $this->createImage($url);
			return static::filePathToUrl($this->resizedPath($image, $image->resizedDims($size)));
		}
		return $url;
	}

	public function responsiveImageSrcset($url, $sizes=array()) {
		if ($this->isResizingEnabled) {
			if (empty($sizes)) $sizes = ['xl','xxl'];
			$image = $this->createImage($url);
			foreach ($sizes as $i => $size) {
				$dims = $image->resizedDims($size);
				$srcset[] = sprintf('%s %dx', static::filePathToUrl($this->resizedPath($image, $dims)), $i + 2);
			}
			return implode(', ', $srcset);
		}
		return '';
	}

	public function getImageMeta($url) {
			$image = (new Imagine())->open(static::filePath($url));
			$dims = $image->getSize();
			$image = null;
			$meta['width'] = $dims->getWidth();
			$meta['height'] = $dims->getHeight();
			return $meta;
	}

	protected function createImage($url) {
		$image = new Image($url);
		$cacheKey = md5($url);
		$cacheItem = $this->metaCache->getItem($cacheKey);
		if ($cacheItem->isHit()) $image->setMeta($cacheItem->get());
		else {
			$meta = $this->getImageMeta($url);
			$cacheItem->set($meta);
			$this->metaCache->save($cacheItem);
			$image->setMeta($meta);
		}
		return $image;
	}

}
