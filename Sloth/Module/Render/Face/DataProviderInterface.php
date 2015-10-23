<?php
namespace Sloth\Module\Render\Face;

interface DataProviderInterface
{
	/**
	 * @var string $name
	 * @return $this
	 */
	public function setName($name);

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @param array $options
	 * @return $this
	 */
	public function setOptions(array $options);

	/**
	 * @return array
	 */
	public function getOptions();

	/**
	 * @return mixed
	 */
	public function getData();
}