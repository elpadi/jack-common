<?php
namespace Jack\Images;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class Image {

	public $path;
	public $dims;

	public static $_sizes = [
		'thumbnail' => 100,
		'tiny' => 160,
		'small' => 320,
		'medium' => 640,
		'large' => 1024,
		'double' => 2048,
	];

	public function __construct($path) {
		$this->path = $path;
	}

	public function setMeta($meta) {
		$this->dims = new Box($meta['width'], $meta['height']);
	}

	public function resizedDims($size) {
		if ($size === 'original') return $this->dims;
		if (!isset(static::$_sizes[$size])) throw new \InvalidArgumentException("Size '$size' not recognized.");
		$w = $this->dims->getWidth();
		$h = $this->dims->getHeight();
		$scale = static::$_sizes[$size] / sqrt($w * $w + $h * $h);
		return $this->dims->scale($scale);
	}

}
