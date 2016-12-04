<?php
namespace Jack;

use Functional as F;
use Imagine\Gd\Imagine;

class Image {

	protected $path;
	protected $filePath;
	public $sizes;
	public $date;
	public $dims;

	public static $_sizes = [
		'thumbnail' => 100,
		'small' => 320,
		'medium' => 640,
		'large' => 1024,
		'double' => 2048,
	];

	public function __construct($path) {
		clearstatcache();
		$this->sizes = [];
		$this->path = $path;
		$this->filePath = realpath(PUBLIC_ROOT_DIR.$path);
		$this->date = filemtime($this->filePath);
		$image = (new Imagine())->open($this->filePath);
		$this->dims = $image->getSize();
	}

	protected function hash($size) {
		return sprintf('assets/cache/%s.%s', md5(serialize([$this->path, $size])), F\last(explode('.', $this->path)));
	}

	protected function isSizeOutdated($size) {
		$imageSize = $this->sizes[$size];
		return !$imageSize->date || ($imageSize->date < $this->date);
	}

	public function addSize($size) {
		$this->sizes[$size] = new ImageSize($this->hash($size), $size === 'original' ? $this->dims : $this->dims->widen(static::$_sizes[$size]));
		if ($this->isSizeOutdated($size)) $this->sizes[$size]->write($this->filePath);
	}

}
