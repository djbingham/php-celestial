<?php
namespace DemoGraph\Module\Graph\Definition\Table;

use DemoGraph\Module\Graph\Definition\Table;
use DemoGraph\Module\Graph\Definition\ValidatorList;

class Field
{
	/**
	 * @var Table
	 */
	public $table;

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
