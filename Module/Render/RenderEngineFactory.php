<?php
namespace Sloth\Module\Render;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Sloth\Module\Render\Engine;
use Sloth\Module\Render\Face\RenderEngineFactoryInterface;

class RenderEngineFactory implements RenderEngineFactoryInterface
{
	/**
	 * @var array
	 */
	protected $engines = array();

	public function getByName($engineName)
	{
		if (!array_key_exists($engineName, $this->engines)) {
			$this->engines[$engineName] = $this->buildEngine($engineName);
		}
		return $this->engines[$engineName];
	}

	protected function buildEngine($engineName)
	{
		$engine = null;
		switch ($engineName) {
			case 'json':
				$engine = new Engine\Json();
				break;
			case 'mustache':
				$engine = new Engine\Mustache();
				break;
			case 'php':
				$engine = new Engine\Php();
				break;
			default:
				throw new InvalidArgumentException(
					'Unrecognised engine name requested from RenderEngineFactory in Render module'
				);
		}
		return $engine;
	}
}
