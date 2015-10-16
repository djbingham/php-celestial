<?php
namespace Sloth\Module\Render\Face;

interface ViewFactoryInterface
{
	/**
	 * @param array $dependencies
	 */
	public function __construct(array $dependencies);

	/**
	 * @param $viewName
	 * @return boolean
	 */
	public function viewExists($viewName);

	/**
	 * @param string $viewName
	 * @return ViewInterface
	 */
	public function getByName($viewName);

	/**
	 * @param array $properties
	 * @return ViewInterface
	 */
	public function build(array $properties);
}