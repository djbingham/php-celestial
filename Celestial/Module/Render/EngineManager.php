<?php
namespace Celestial\Module\Render;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Celestial\Module\Render\Engine;
use Celestial\Module\Render\Face\EngineManagerInterface;
use Celestial\Module\Render\Face\RenderEngineInterface;

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
