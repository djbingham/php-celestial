<?php
namespace Sloth\Module\Render\Face;

use Sloth\Helper\Face\ObjectListInterface;

interface ViewListInterface extends ObjectListInterface
{
	/**
	 * @param ViewInterface $view
	 * @return $this
	 */
	public function push(ViewInterface $view);

	/**
	 * @param int $index
	 * @return ViewInterface
	 */
	public function getByIndex($index);

	/**
	 * @param string $propertyName
	 * @param mixed $value
	 * @return ViewInterface
	 */
	public function getByProperty($propertyName, $value);
}