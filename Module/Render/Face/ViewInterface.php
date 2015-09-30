<?php
namespace Sloth\Module\Render\Face;

use Module\Render\Face\DataProviderListInterface;

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

	/**
	 * @return DataProviderListInterface
	 */
	public function getDataProviders();
}
