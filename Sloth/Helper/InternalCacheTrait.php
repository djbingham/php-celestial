<?php
namespace Helper;

trait InternalCacheTrait
{
	private $cache = array();

	protected function setCached($name, $value)
	{
		if (is_array($name)) {
			$subCachePath = $name;
			$name = array_pop($subCachePath);
			$this->setSubCached($subCachePath, $name, $value);
		} else {
			$this->cache[$name] = $value;
		}
		return $this;
	}

	protected function getCached($name)
	{
		$value = null;
		if ($this->isCached($name)) {
			if (is_array($name)) {
				$subCachePath = $name;
				$name = array_pop($subCachePath);
				$value = $this->getSubCached($subCachePath, $name);
			} else {
				$value = $this->cache[$name];
			}
		}
		return $value;
	}

	protected function isCached($name)
	{
		if (is_array($name)) {
			$subCachePath = $name;
			$name = array_pop($subCachePath);
			$cached = $this->isSubCached($subCachePath, $name);
		} else {
			$cached = array_key_exists($name, $this->cache);
		}
		return $cached;
	}

	private function setSubCached(array $subCachePath, $name, $value)
	{
		$subCache = &$this->cache;
		while (!empty($subCachePath)) {
			$nextName = array_shift($subCachePath);
			if (!array_key_exists($nextName, $subCache)) {
				$subCache[$nextName] = array();
			}
			$subCache = &$subCache[$nextName];
		}
		$subCache[$name] = $value;
		return $subCache;
	}

	private function getSubCached(array $subCachePath, $name)
	{
		$subCache = &$this->cache;
		while (!empty($subCachePath)) {
			$nextName = array_shift($subCachePath);
			if (!array_key_exists($nextName, $subCache) || !is_array($subCache[$nextName])) {
				$subCache = array();
				break;
			}
			$subCache = &$subCache[$nextName];
		}
		return $subCache[$name];
	}

	private function isSubCached(array $subCachePath, $name)
	{
		$subCache = &$this->cache;
		while (!empty($subCachePath)) {
			$nextName = array_shift($subCachePath);
			if (!array_key_exists($nextName, $subCache) || !is_array($subCache[$nextName])) {
				$subCache = array();
				break;
			}
			$subCache = &$subCache[$nextName];
		}
		return array_key_exists($name, $subCache);
	}
}