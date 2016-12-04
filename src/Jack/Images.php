<?php
namespace Jack;

class Images {

	public static function resizeImage($path, $size) {
		$image = new Image($path);
		$image->addSize($size);
		return PUBLIC_ROOT.$image->sizes[$size]->hash;
	}

	public static function responsiveImageSrcset($path, $maxSize) {
		$image = new Image($path);
		$maxWidth = $maxSize === 'original' ? $image->dims->getWidth() : Image::$_sizes[$maxSize];
		foreach (array_unique(array_merge(['original' => $image->dims->getWidth()], array_reverse(Image::$_sizes))) as $size => $width) if ($width > 300 && $width <= $maxWidth) {
			$image->addSize($size);
			$srcset[] = sprintf('%s %dw', PUBLIC_ROOT.$image->sizes[$size]->hash, $width);
		}
		return $srcset;
	}

}
