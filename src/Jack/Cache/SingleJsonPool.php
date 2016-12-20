<?php
namespace Jack\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;

class SingleJsonPool implements CacheItemPoolInterface {

	protected $_values;
	private $createCacheItem;

	public function __construct($name) {
		$this->path = sprintf('%s/cache/%s.json', JACK_DIR, $name);
		$this->createCacheItem = \Closure::bind(
			function ($key, $value, $isHit) {
				$item = new CacheItem();
				$item->key = $key;
				$item->value = $value;
				$item->isHit = $isHit;
				$item->defaultLifetime = 0;
				return $item;
			},
			null,
			CacheItem::class
		);
	}

	protected function fetchValues() {
		if (is_array($this->_values)) return null;
		if (file_exists($this->path)) {
			$this->_values = json_decode(file_get_contents($this->path), TRUE);
		}
		else $this->_values = [];
	}

	public function getItem($key) {
		$this->fetchValues();
		$f = $this->createCacheItem;
		return $f($key, isset($this->_values[$key]) ? $this->_values[$key] : null, isset($this->_values[$key]));
	}

	public function getItems(array $keys = array()) {
		return array_map([$this, 'getItem'], $keys);
	}

	public function hasItem($key) {
		$this->fetchValues();
		return isset($this->_values[$key]);
	}

	public function clear() {
		throw new \LogicException("This method should never be called.");
	}

	public function deleteItem($key) {
		throw new \LogicException("This method should never be called.");
	}

	public function deleteItems(array $keys) {
		throw new \LogicException("This method should never be called.");
	}

	public function save(CacheItemInterface $item) {
		$this->_values[$item->getKey()] = $item->get();
		return file_put_contents($this->path, json_encode($this->_values));
	}

	public function saveDeferred(CacheItemInterface $item) {
		throw new \LogicException("This method should never be called.");
	}

	public function commit() {
		throw new \LogicException("This method should never be called.");
	}

}
