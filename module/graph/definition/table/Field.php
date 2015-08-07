<?php
namespace Sloth\Module\Graph\Definition\Table;

use Sloth\Module\Graph\Definition\Table;
use Sloth\Module\Graph\Definition\ValidatorList;

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
