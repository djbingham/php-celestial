<?php
namespace Sloth\Module\Render\Face;

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
	 * @return string
	 */
	public function getEngineName();
}
