<?php
namespace Sloth\Module\Graph;

interface ResourceInterface
{
	/**
	 * @param ResourceFactoryInterface $factory
	 */
	public function __construct(ResourceFactoryInterface $factory);

	/**
	 * @return $this
	 */
	public function save();

	/**
	 * @return $this
	 */
	public function delete();

	/**
	 * @param array $values New attribute values, keyed by attribute name
	 * @return $this
	 */
	public function setAttributes(array $values);

	/**
	 * @return array Attribute values, keyed by attribute name
	 */
	public function getAttributes();

	/**
	 * @param string $name Attribute name
	 * @param mixed $value New attribute value
	 * @return $this
	 */
	public function setAttribute($name, $value);

	/**
	 * @param string $name Attribute name
	 * @return mixed Current attribute value
	 */
	public function getAttribute($name);
}