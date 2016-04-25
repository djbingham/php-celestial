<?php
namespace Sloth\Module\Render;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Sloth\Module\Render\Engine;
use Sloth\Module\Render\Face\EngineManagerInterface;
use Sloth\Module\Render\Face\RenderEngineInterface;

class EngineManager implements EngineManagerInterface
{
	/**
	 * @var array
	 */
	protected $engines = array();

	public function registerEngine($engineName, RenderEngineInterface $engine)
	{
		$this->engines[$engineName] = $engine;
		return $this;
	}

	public function getByName($engineName)
	{
		if (!array_key_exists($engineName, $this->engines)) {
			throw new InvalidArgumentException(
				'Unrecognised engine name requested from EngineManager in Render module'
			);
		}
		return $this->engines[$engineName];
	}
}
