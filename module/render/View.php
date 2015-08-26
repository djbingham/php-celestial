<?php
namespace Sloth\Module\Render;

use Sloth\Module\Render\Face\ViewInterface;

class View implements ViewInterface
{
	public $name;
	public $path;
	public $engine;

	public function getName()
	{
		return $this->name;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getEngineName()
	{
		return $this->engine;
	}

	public function getFunctionName()
	{
		if (preg_match('/\./', $this->name)) {
			$extensionStartPos = strrpos($this->name, '.');
			$function = lcfirst(substr($this->name, 0, $extensionStartPos));
		} else {
			$function = $this->name;
		}
		return $function;
	}

	public function getNameExtension()
	{
		$extensionStartPos = strrpos($this->name, '.');
		$extension = null;
		if ($extensionStartPos !== false) {
			$extension = strToLower(substr($this->name, $extensionStartPos + 1));
		}
		return $extension;
	}

	public function getPathExtension()
	{
		$extensionStartPos = strrpos($this->path, '.');
		$extension = null;
		if ($extensionStartPos !== false) {
			$extension = strToLower(substr($this->path, $extensionStartPos + 1));
		}
		return $extension;
	}
}
