<?php
namespace Jack;

use Imagine\Gd\Imagine;

class ImageSize {

	protected $filePath;
	public $hash;
	public $date;
	public $dims;

	public function __construct($hash, $dims) {
		$this->hash = $hash;
		$this->dims = $dims;
		$this->refreshMetadata();
	}

	protected function refreshMetadata() {
		if (is_file(PUBLIC_ROOT_DIR.'/'.$this->hash)) {
			$this->filePath = realpath(PUBLIC_ROOT_DIR.'/'.$this->hash);
			$this->date = filemtime($this->filePath);
		}
		else {
			$this->filePath = '';
			$this->date = 0;
		}
	}

	public function write($filePath) {
		(new Imagine())->open($filePath)->resize($this->dims)->save(PUBLIC_ROOT_DIR.'/'.$this->hash);
		$this->refreshMetadata();
	}

}
