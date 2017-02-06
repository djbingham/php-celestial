<?php
namespace Sloth\Module\Render;

use Sloth\Module\Render\Face\RenderEngineInterface;
use Sloth\Module\Render\Face\ViewInterface;

class View implements ViewInterface
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $path;

	/**
	 * @var RenderEngineInterface
	 */
	public $engine;

	/**
	 * @var array
	 */
	public $dataProviders;

	/**
	 * @var array
	 */
	public $options;

	public function getName()
	{
		return $this->name;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getEngine()
	{
		return $this->engine;
	}

	public function getDataProviders()
	{
		return $this->dataProviders;
	}

	public function getOptions()
	{
		return $this->options;
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
