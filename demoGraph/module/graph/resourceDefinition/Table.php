<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

class Table
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $alias;

	/**
	 * @var array
	 */
	public $fields = array();

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
