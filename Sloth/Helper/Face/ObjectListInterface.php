<?php
namespace Helper\Face;

interface ObjectListInterface extends \Iterator
{
	/**
	 * @return integer
	 */
	public function length();

	/**
	 * @param integer $index
	 * @return mixed
	 */
	public function getByIndex($index);

	/**
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @return mixed
	 */
	public function getByProperty($propertyName, $propertyValue);

	/**
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @return integer
	 */
	public function indexOfPropertyValue($propertyName, $propertyValue);

	/**
	 * @param integer $index
	 * @return $this
	 */
	public function removeByIndex($index);

	/**
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @return $this
	 */
	public function removeByPropertyValue($propertyName, $propertyValue);

	/**
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @return ObjectListInterface
	 */
	public function findByPropertyValue($propertyName, $propertyValue);
}
