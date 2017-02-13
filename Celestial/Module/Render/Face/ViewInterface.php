<?php
namespace Celestial\Module\Render\Face;

interface ViewInterface
{
	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string
	 */
	public function getPath();

	/**
	 * @return RenderEngineInterface
	 */
	public function getEngine();

	/**
	 * @return array
	 */
	public function getDataProviders();

	/**
	 * @return array
	 */
	public function getOptions();
}
