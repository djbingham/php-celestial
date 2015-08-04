<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\ResourceDefinition\Resource as GraphResource;

class Attribute
{
	/**
	 * @var GraphResource
	 */
	public $resource;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $alias;

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var ValidatorList
	 */
	public $validators = array();

	public function getAlias()
	{
		$alias = null;
		if ($this->alias !== null) {
			$alias = $this->alias;
		} else {
			$alias = $this->name;
		}
		return $alias;
	}
}
