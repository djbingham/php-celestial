<?php
namespace Sloth\Module\Render\Face;

interface EngineManagerInterface
{
	/**
	 * @param string $engineName
	 * @param RenderEngineInterface $engine
	 * @return $this
	 */
	public function registerEngine($engineName, RenderEngineInterface $engine);

	/**
	 * @param string $engineName
	 * @return RenderEngineInterface
	 */
	public function getByName($engineName);
}