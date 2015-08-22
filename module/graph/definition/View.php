<?php
namespace Sloth\Module\Graph\Definition;

class View
{
	public $name;
	public $path;
	public $engine;

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
