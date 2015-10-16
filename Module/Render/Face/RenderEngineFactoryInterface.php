<?php
namespace Sloth\Module\Render\Face;

interface RenderEngineFactoryInterface
{
	/**
	 * @param string $engineName
	 * @return RenderEngineInterface
	 */
	public function getByName($engineName);
}