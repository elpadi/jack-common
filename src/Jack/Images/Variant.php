<?php
namespace Jack\Images;

use Functional as F;
use Imagine\Gd\Imagine;
use Imagine\Gd\Image as ImagineImage;
use Imagine\Image\Box;

class Variant {

	public $image;
	public $dims;

	public function __construct(Image $image, Box $dims) {
		$this->image = $image;
		$this->dims = $dims;
	}

	public function save($path) {
		return
			(new Imagine())->open(Manager::filePath($this->image->path))
			->resize($this->dims)->save($path);
	}

}
