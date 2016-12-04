<?php
namespace Jack;
use Functional as F;

class Images {

	protected static function imageSizes($size=null) {
		$sizes = [
			'thumbnail' => 100,
			'small' => 320,
			'medium' => 640,
			'large' => 1024,
			'double' => 2048,
		];
		if ($size && !in_array($size, array_keys($sizes))) throw new \InvalidArgumentException("Invalid size '$size'.");
		return $size ? $sizes[$size] : array_reverse($sizes);
	}

	protected static function loadImagine() {
		if (extension_loaded('imagick')) return new \Imagine\Imagick\Imagine();
		return new \Imagine\Gd\Imagine();
	}

	public static function resizeImage($path, $size) {
		$filePath = realpath(PUBLIC_ROOT_DIR.$path);
		$hash = sprintf('assets/cache/%s.%s', md5(serialize(func_get_args())), F\last(explode('.', $path)));
		$hashFile = PUBLIC_ROOT_DIR.'/'.$hash;
		if (is_file($hashFile) && filemtime($hashFile) > filemtime($filePath)) return PUBLIC_ROOT.$hash;
		try {
			$image = static::loadImagine()->open($filePath);
		}
		catch (\Exception $e) {
			if (DEBUG) {
				var_dump(__FILE__.":".__LINE__." - ".__METHOD__, $e);
				exit(0);
			}
			return PUBLIC_ROOT.$path;
		}
		$newWidth = $size === 'original' ? $image->getSize()->getWidth() : static::imageSizes($size);
		if ($newWidth < $image->getSize()->getWidth()) $image = $image->resize($image->getSize()->widen($newWidth));
		$image->save($hashFile);
		return PUBLIC_ROOT.$hash;
	}

	public static function responsiveImageSrcset($path) {
		$filePath = realpath(PUBLIC_ROOT_DIR.$path);
		$image = static::loadImagine()->open($filePath);
		$width = $image->getSize()->getWidth();
		$image = null;
		foreach (static::imageSizes() as $s => $w) {
			if ($w > 300 && $w < $width) $srcset[] = sprintf('%s %dw', static::resizeImage($path, $s), $w);
		}
		$srcset[] = sprintf('%s %dw', static::resizeImage($path, 'original'), $width);
		return $srcset;
	}

}
